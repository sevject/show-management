<?php
/**
 * Created by PhpStorm.
 * User: tina_hammer
 * Date: 09.12.14
 * Time: 14:29
 */

require_once(__DIR__ . '/../../SingeltonDB.php');
require_once(__DIR__ . '/../EpisodeInsertScript.php');

class StoreResource
{
    private $action;

    public function doAction()
    {
        $this->action = $_REQUEST['action'];

        switch($this->action) {
            case 'loadSeries':
                $this->loadSeries();
                break;
            case 'loadEpisodes':
                $this->loadEpisodes();
                break;
            case 'saveEpisodeStatus':
                $this->saveEpisodeStatus();
                break;
            case 'loadSeasons':
                $this->loadSeasons();
                break;
            case 'saveNewSeries':
                $this->saveNewSeries();
                break;
            case 'deleteSeries':
                $this->deleteSeries();
                break;
            case 'getTVDBSeries':
                $this->getTVDBSeries();
                break;
            default:
                throw new Exception('DU PFEIFE HAST NE ACTION VERGESSEN');
                break;
        }
    }

    private function loadSeries()
    {
        $sql = SingeltonDB::generalDBConnection();

        $result = [[
            'SeriesID' => 0,
            'SeriesTitle' => 'ALL'
        ]];

        $query = "SELECT
                        *
                    FROM
                        `Series`
                    ORDER BY
                        `SeriesTitle`
        ";
        $sql->query($query);

        $this->createStoreOutput(array_merge($result, $sql->vResultArray()));
    }

    private function createStoreOutput($data, $total = null)
    {
        $total = $total !== null ? $total : count($data);

        $output = [
            'success' => true,
            'rows' => $data,
            'total' => $total
        ];

        echo json_encode($output);
    }

    private function loadEpisodes()
    {
        $season = $_REQUEST['Season'];
        $start = $_REQUEST['start'];
        $limit = $_REQUEST['limit'];
        $SeriesID = $_REQUEST['SeriesID'];
        $watched = $_REQUEST['Watched'];

        $limitstart = $start;
        $limitend = $limit;

        $SeriesID = $SeriesID ?: 0;
        $season = $season != 'ALL' ? $season : -1;

        $sql = SingeltonDB::generalDBConnection();
        $query = " SELECT SQL_CALC_FOUND_ROWS
                        *
                    FROM
                        `Episodes`
                    WHERE
                        [seriesId] IN(`SeriesID`, 0)
                        AND
                        [Season] IN(`Season`, -1)
                        AND
                        [Watched] IN(`Watched`, -1)
                    ORDER BY
                        `Season`,
                        `EpisodeNr`
                    LIMIT
                        {limitstart}, {limitend}
        ";
        $sql->query(
            $query,
            [
                "limitstart" => $limitstart,
                "limitend" => $limitend,
                "seriesId" => $SeriesID,
                "Season" => $season,
                "Watched" => $watched
            ]
        );

        $total = $sql->getFoundRows();

        $this->createStoreOutput($sql->vResultArray(), $total);
    }

    private function loadSeasons()
    {
        $series_id = $_REQUEST['SeriesID'];
        $sql = SingeltonDB::generalDBConnection();

        $result = [[
            'Season' => 'ALL'
        ]];


        $query = " SELECT DISTINCT
                        `Season`
                    FROM
                        `Episodes`
                    WHERE
                        `SeriesID` = [series_id]
                    ORDER BY
                        `Season`
        ";
        $sql->query(
            $query,
            [
                "series_id" => $series_id
            ]
        );

        $this->createStoreOutput(array_merge($result, $sql->vResultArray()));
    }

    private function saveEpisodeStatus()
    {
        $data = json_decode($_POST['save_data']);
        $watched = [];
        $notwatched= [];

        foreach ($data as $key => $value) {
            if ($value) {
                $watched[] = $key;
            } else {
                $notwatched[] =  $key;
            }
        }

        $sql = SingeltonDB::generalDBConnection();

        foreach([true, false] as $value) {
            $query = " UPDATE
                            `Episodes`
                        SET
                            `Watched` = [value]
                        WHERE
                            `EpisodeID` IN [episode_ids]
            ";
            $sql->query($query,
                [
                    "value" => $value,
                    "episode_ids" => $value ? $watched : $notwatched
                ]
            );
        }
    }

    private function saveNewSeries()
    {
        $title = $_REQUEST['title'];
        $tvdbid = $_REQUEST['tvdbid'];

        $sql = SingeltonDB::generalDBConnection();

        $query = " SELECT `TheTVDBSeriesID`, `SeriesID`
                    FROM `Series`
                    WHERE `TheTVDBSeriesID` = [tvdb_id]
                    ";
        $sql->query($query, ['tvdb_id' => $tvdbid]);
        $existing_series_id = $sql->vAssoc();

        $ep_insert = new EpisodeInsertScript();

        if ($existing_series_id["TheTVDBSeriesID"] <= 0) {
            $query = " INSERT INTO
                        `Series`
                    SET
                        `TheTVDBSeriesID` = [tvdb_id],
                        `SeriesTitle` = [title]
                        ";
            $sql->query($query,
                [
                    "tvdb_id" => $tvdbid,
                    "title" => $title
                ]
            );
            $ep_insert->insertEpisodes($sql->vLastID(), $tvdbid);
        } else {
            $ep_insert->insertEpisodes($existing_series_id['SeriesID'], $tvdbid);
        }
    }

    private function deleteSeries()
    {
        $series_id = $_REQUEST['seriesid'];

        $sql = SingeltonDB::generalDBConnection();
        $query = " DELETE
                        `Series`,
                        `Episodes`
                    FROM
                        `Series`,
                        `Episodes`
                    WHERE
                        `Series`.`SeriesID` = [series_id]
                    AND
                        `Episodes`.`SeriesID` = [series_id]
        ";
        $sql->query(
            $query,
            [
                "series_id" => $series_id
            ]
        );
    }

    private function getTVDBSeries()
    {
        $query = $_REQUEST['query'];

        $url = 'http://thetvdb.com/api/GetSeries.php?seriesname=' . urlencode($query);

        $magic = new EpisodeInsertScript();

        $data =  $magic->xml2array($url);
        $storeData = [];

        if (!empty($data['Data'])) {
            if ($data['Data']['Series']['seriesid']) {
                $series = $data['Data']['Series'];
                $data['Data']['Series'] = [$series];
            }

            foreach($data['Data']['Series'] as $series) {
                array_push($storeData, [
                    'TVDBID' => $series['seriesid'],
                    'SeriesName' => $series['SeriesName']
                ]);
            }
        }

        $this->createStoreOutput($storeData);
    }
}

$obj = new StoreResource();
$obj->doAction();
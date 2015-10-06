<?php
/**
 * Created by PhpStorm.
 * User: tina_hammer
 * Date: 10.12.14
 * Time: 11:09
 */

require_once(__DIR__ . '/../SingeltonDB.php');

class EpisodeInsertScript
{
    //http://thetvdb.com/api/74C646F2A4937AE5/series/80348/all/en.xml

    public function xml2array($url, $get_attributes = 1, $priority = 'tag')
    {
        $contents = "";
        if (!function_exists('xml_parser_create'))
        {
            return array ();
        }
        $parser = xml_parser_create('');
        if (!($fp = @ fopen($url, 'rb')))
        {
            return array ();
        }
        while (!feof($fp))
        {
            $contents .= fread($fp, 8192);
        }
        fclose($fp);
        xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
        xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
        xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
        xml_parse_into_struct($parser, trim($contents), $xml_values);
        xml_parser_free($parser);
        if (!$xml_values)
            return; //Hmm...
        $xml_array = array ();
        $parents = array ();
        $opened_tags = array ();
        $arr = array ();
        $current = & $xml_array;
        $repeated_tag_index = array ();
        foreach ($xml_values as $data)
        {
            unset ($attributes, $value);
            extract($data);
            $result = array ();
            $attributes_data = array ();
            if (isset ($value))
            {
                if ($priority == 'tag')
                    $result = $value;
                else
                    $result['value'] = $value;
            }
            if (isset ($attributes) and $get_attributes)
            {
                foreach ($attributes as $attr => $val)
                {
                    if ($priority == 'tag')
                        $attributes_data[$attr] = $val;
                    else
                        $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
                }
            }
            if ($type == "open")
            {
                $parent[$level -1] = & $current;
                if (!is_array($current) or (!in_array($tag, array_keys($current))))
                {
                    $current[$tag] = $result;
                    if ($attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    $current = & $current[$tag];
                }
                else
                {
                    if (isset ($current[$tag][0]))
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else
                    {
                        $current[$tag] = array (
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 2;
                        if (isset ($current[$tag . '_attr']))
                        {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset ($current[$tag . '_attr']);
                        }
                    }
                    $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                    $current = & $current[$tag][$last_item_index];
                }
            }
            elseif ($type == "complete")
            {
                if (!isset ($current[$tag]))
                {
                    $current[$tag] = $result;
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $attributes_data)
                        $current[$tag . '_attr'] = $attributes_data;
                }
                else
                {
                    if (isset ($current[$tag][0]) and is_array($current[$tag]))
                    {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                        if ($priority == 'tag' and $get_attributes and $attributes_data)
                        {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                        $repeated_tag_index[$tag . '_' . $level]++;
                    }
                    else
                    {
                        $current[$tag] = array (
                            $current[$tag],
                            $result
                        );
                        $repeated_tag_index[$tag . '_' . $level] = 1;
                        if ($priority == 'tag' and $get_attributes)
                        {
                            if (isset ($current[$tag . '_attr']))
                            {
                                $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                                unset ($current[$tag . '_attr']);
                            }
                            if ($attributes_data)
                            {
                                $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                            }
                        }
                        $repeated_tag_index[$tag . '_' . $level]++; //0 and 1 index is already taken
                    }
                }
            }
            elseif ($type == 'close')
            {
                $current = & $parent[$level -1];
            }
        }
        return ($xml_array);
    }

    public function insertEpisodes($seriesId, $thetvdbSeriesId)
    {

        $data = $this->xml2array('http://thetvdb.com/api/74C646F2A4937AE5/series/' . $thetvdbSeriesId . '/all/en.xml');

        $sql = SingeltonDB::generalDBConnection();

        $query = "UPDATE
                    `Series`
                  SET
                    `SeriesBanner` = [banner]
                  WHERE
                    `SeriesID` = [seriesId]
                  ";
        $sql->query($query, [
            'banner' => $data['Data']['Series']['banner'],
            'seriesId' => $seriesId
        ]);

        $selectQuery = "SELECT CONCAT (`Season`, `EpisodeNr`) AS `SeasonEp`
                    FROM `Episodes`
                    WHERE `SeriesID` = [seriesId]
                    ";
        $sql->query($selectQuery, ['seriesId' => $seriesId]);
        $existing_episodes = $sql->vResultArray();
        $existingSeasonEp = [];
        foreach ($existing_episodes as $key => $value) {
            $existingSeasonEp[] = $value['SeasonEp'];
        }

        $insertQuery = " INSERT INTO
                                `Episodes`
                            SET
                                `Title` = [EpisodeName],
                                `Season` = [SeasonNumber],
                                `EpisodeNr`= [EpisodeNumber],
                                `AirDate` = [FirstAired],
                                `SeriesID` = [series_id],
                                `Description` = [Overview]
                                ";

        $updateQuery = " UPDATE `Episodes`
                            SET
                                `Title` = [EpisodeName],
                                `AirDate` = [FirstAired],
                                `Description` = [Overview]
                            WHERE
                                `SeriesID` = [series_id]
                                AND
                                `Season` = [SeasonNumber]
                                AND
                                `EpisodeNr` = [EpisodeNumber]
                            ";

        foreach ($data['Data']['Episode'] as $episode) {
            $TVDBSeasonEp = $episode['SeasonNumber'] . $episode['EpisodeNumber'];
            $episode['series_id'] = $seriesId;
            if (!in_array($TVDBSeasonEp, $existingSeasonEp)) {
                $sql->query($insertQuery, $episode);
            } elseif (in_array($TVDBSeasonEp, $existingSeasonEp)) {
                $sql->query($updateQuery, $episode);
            }
        }
    }
}

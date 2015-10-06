/*
 * File: app/view/MyViewport.js
 *
 * This file was generated by Sencha Architect version 3.1.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.2.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.2.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('TestModule.view.MyViewport', {
    extend: 'Ext.container.Viewport',

    requires: [
        'Ext.grid.Panel',
        'Ext.grid.column.Date',
        'Ext.grid.column.CheckColumn',
        'Ext.grid.View',
        'Ext.Img',
        'Ext.form.FieldSet',
        'Ext.form.field.ComboBox',
        'Ext.toolbar.Spacer',
        'Ext.button.Button',
        'Ext.form.RadioGroup',
        'Ext.form.field.Radio',
        'Ext.toolbar.Fill',
        'Ext.grid.plugin.CellEditing',
        'Ext.toolbar.Paging'
    ],

    layout: 'fit',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'panel',
                    layout: 'fit',
                    title: '',
                    items: [
                        {
                            xtype: 'gridpanel',
                            itemId: 'grid',
                            autoScroll: true,
                            title: '',
                            store: 'EpisodeStore',
                            columns: [
                                {
                                    xtype: 'gridcolumn',
                                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                        var rec,
                                            seriesCombo = Ext.ComponentQuery.query('#seriesCombo')[0],
                                            index = seriesCombo.getStore().findExact('SeriesID', record.get('SeriesID'));

                                        if (index !== -1) {
                                            rec = seriesCombo.getStore().getAt(index);

                                            return rec.get('SeriesTitle');
                                        }
                                    },
                                    dataIndex: 'SeriesTitle',
                                    text: 'Series',
                                    flex: 1
                                },
                                {
                                    xtype: 'gridcolumn',
                                    dataIndex: 'Season',
                                    text: 'Season'
                                },
                                {
                                    xtype: 'gridcolumn',
                                    dataIndex: 'EpisodeNr',
                                    text: 'Nr.'
                                },
                                {
                                    xtype: 'gridcolumn',
                                    renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
                                        metaData.tdAttr = 'data-qtip="' + record.get('Description') + '"';
                                        return value;
                                    },
                                    dataIndex: 'Title',
                                    text: 'Title',
                                    flex: 2
                                },
                                {
                                    xtype: 'datecolumn',
                                    dataIndex: 'AirDate',
                                    text: 'First Aired',
                                    flex: 1,
                                    format: 'd.m.Y'
                                },
                                {
                                    xtype: 'checkcolumn',
                                    dataIndex: 'Watched',
                                    text: 'Watched',
                                    editor: {
                                        xtype: 'checkboxfield'
                                    }
                                }
                            ],
                            dockedItems: [
                                {
                                    xtype: 'toolbar',
                                    dock: 'top',
                                    items: [
                                        {
                                            xtype: 'image',
                                            height: 55,
                                            itemId: 'seriesBanner',
                                            width: 300
                                        },
                                        {
                                            xtype: 'fieldset',
                                            title: 'Filter',
                                            layout: {
                                                type: 'hbox',
                                                align: 'stretch',
                                                padding: 5
                                            },
                                            items: [
                                                {
                                                    xtype: 'combobox',
                                                    flex: 1,
                                                    itemId: 'seriesCombo',
                                                    fieldLabel: 'Series:',
                                                    labelWidth: 50,
                                                    value: 0,
                                                    emptyText: 'All Series',
                                                    selectOnFocus: true,
                                                    displayField: 'SeriesTitle',
                                                    store: 'SeriesStore',
                                                    valueField: 'SeriesID',
                                                    listeners: {
                                                        change: {
                                                            fn: me.onComboboxChange,
                                                            scope: me
                                                        }
                                                    }
                                                },
                                                {
                                                    xtype: 'tbspacer',
                                                    flex: 1,
                                                    width: 5
                                                },
                                                {
                                                    xtype: 'button',
                                                    handler: function(button, e) {
                                                        var seriesCombo = Ext.ComponentQuery.query('#seriesCombo')[0],
                                                            grid = Ext.ComponentQuery.query('#grid')[0];

                                                        if (seriesCombo.getValue()) {
                                                            Ext.Msg.confirm('Warning', 'Are you sure you want to delete ' + seriesCombo.getRawValue() + '?', function(answer) {
                                                                if (answer === 'yes') {
                                                                    Ext.Ajax.request({
                                                                        url: '/TestModule/StoreResource.php',
                                                                        params: {
                                                                            'action' : 'deleteSeries',
                                                                            'seriesid' : seriesCombo.getValue()
                                                                        },
                                                                        success: function() {
                                                                            seriesCombo.getStore().reload();
                                                                            grid.getStore().reload();
                                                                        }
                                                                    });
                                                                }
                                                            });
                                                        }
                                                    },
                                                    icon: '/TestModule/resources/delete.png'
                                                },
                                                {
                                                    xtype: 'tbspacer',
                                                    flex: 1,
                                                    width: 20
                                                },
                                                {
                                                    xtype: 'combobox',
                                                    flex: 1,
                                                    disabled: true,
                                                    itemId: 'seasonCombo',
                                                    fieldLabel: 'Season:',
                                                    labelWidth: 50,
                                                    value: 'ALL',
                                                    editable: false,
                                                    allQuery: '\'\'',
                                                    displayField: 'Season',
                                                    queryCaching: false,
                                                    store: 'SeasonStore',
                                                    valueField: 'Season',
                                                    listeners: {
                                                        change: {
                                                            fn: me.onComboboxChange1,
                                                            scope: me
                                                        }
                                                    }
                                                },
                                                {
                                                    xtype: 'tbspacer',
                                                    flex: 1,
                                                    width: 15
                                                },
                                                {
                                                    xtype: 'radiogroup',
                                                    flex: 1,
                                                    itemId: 'watchedState',
                                                    width: 200,
                                                    layout: {
                                                        type: 'checkboxgroup',
                                                        autoFlex: false
                                                    },
                                                    items: [
                                                        {
                                                            xtype: 'radiofield',
                                                            margin: '0 10 0 0',
                                                            name: 'watchedState',
                                                            boxLabel: 'All',
                                                            checked: true,
                                                            inputValue: '-1'
                                                        },
                                                        {
                                                            xtype: 'radiofield',
                                                            margin: '0 10 0 0',
                                                            name: 'watchedState',
                                                            boxLabel: 'Watched',
                                                            inputValue: '1'
                                                        },
                                                        {
                                                            xtype: 'radiofield',
                                                            name: 'watchedState',
                                                            boxLabel: 'Not Watched',
                                                            inputValue: '0'
                                                        }
                                                    ],
                                                    listeners: {
                                                        change: {
                                                            fn: me.onRadiogroupChange,
                                                            scope: me
                                                        }
                                                    }
                                                }
                                            ]
                                        },
                                        {
                                            xtype: 'tbspacer',
                                            width: 25
                                        },
                                        {
                                            xtype: 'fieldset',
                                            padding: 5,
                                            title: 'TheTVDB',
                                            layout: {
                                                type: 'hbox',
                                                align: 'stretch'
                                            },
                                            items: [
                                                {
                                                    xtype: 'combobox',
                                                    itemId: 'findSeriesCombo',
                                                    width: 350,
                                                    fieldLabel: 'Find Series',
                                                    labelWidth: 60,
                                                    displayField: 'SeriesName',
                                                    store: 'TVDBStore',
                                                    valueField: 'TVDBID'
                                                },
                                                {
                                                    xtype: 'tbspacer',
                                                    flex: 1,
                                                    width: 5
                                                },
                                                {
                                                    xtype: 'button',
                                                    handler: function(button, e) {
                                                        var myMask,
                                                            findSeriesCombo = Ext.ComponentQuery.query('#findSeriesCombo')[0],
                                                            NewSeriesTitle = findSeriesCombo.getRawValue(),
                                                            TheTVDBID = findSeriesCombo.getValue(),
                                                            seriesCombo = Ext.ComponentQuery.query('#seriesCombo')[0],
                                                            grid = Ext.ComponentQuery.query('#grid')[0];

                                                        if (TheTVDBID) {

                                                            myMask = new Ext.LoadMask({target: grid, msg:"Please wait..."});
                                                            myMask.show();

                                                            Ext.Ajax.request({
                                                                url: '/TestModule/StoreResource.php',
                                                                params: {
                                                                    'action' : 'saveNewSeries',
                                                                    'title' : NewSeriesTitle,
                                                                    'tvdbid' : TheTVDBID
                                                                },
                                                                success: function() {
                                                                    findSeriesCombo.clearValue();

                                                                    seriesCombo.getStore().load();

                                                                    grid.getStore().on('load', function() {
                                                                        myMask.hide();
                                                                    });

                                                                    grid.getStore().clearFilter();
                                                                    grid.getStore().load();
                                                                },
                                                                failure: function() {
                                                                    myMask.hide();
                                                                }
                                                            });
                                                        }
                                                    },
                                                    flex: 1,
                                                    text: 'Import'
                                                }
                                            ]
                                        },
                                        {
                                            xtype: 'tbfill'
                                        },
                                        {
                                            xtype: 'button',
                                            handler: function(button, e) {
                                                var grid = Ext.ComponentQuery.query('#grid')[0],
                                                    data = {};

                                                grid.getStore().getRange().forEach(function(record) {
                                                    data[record.get('EpisodeID')] = record.get('Watched');
                                                });

                                                Ext.Ajax.request({
                                                    url: '/TestModule/StoreResource.php',
                                                    params: {
                                                        'action' : 'saveEpisodeStatus',
                                                        'save_data' : Ext.encode(data)
                                                    },
                                                    success: function() {
                                                        grid.getStore().reload();
                                                    }
                                                });
                                            },
                                            width: 100,
                                            icon: '/TestModule/resources/disk_green.png',
                                            iconAlign: 'top',
                                            scale: 'medium',
                                            text: 'Save'
                                        }
                                    ]
                                },
                                {
                                    xtype: 'pagingtoolbar',
                                    dock: 'bottom',
                                    width: 360,
                                    displayInfo: true,
                                    store: 'EpisodeStore'
                                }
                            ],
                            plugins: [
                                Ext.create('Ext.grid.plugin.CellEditing', {

                                })
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },

    onComboboxChange: function(field, newValue, oldValue, eOpts) {
        var rec, index,
            grid = Ext.ComponentQuery.query('#grid')[0],
            seasonCombo = Ext.ComponentQuery.query('#seasonCombo')[0],
            seriesBanner = Ext.ComponentQuery.query('#seriesBanner')[0];

        if (newValue === 0) {
            seasonCombo.setValue('ALL');
        }

        seasonCombo.setDisabled(newValue === 0);

        if (grid) {
            grid.getStore().loadPage(1);

            index = field.getStore().findExact('SeriesID', field.getValue());

            if (index !== -1) {
                rec = field.getStore().getAt(index);

                if (!Ext.isEmpty(rec.get('SeriesBanner'))) {
                    seriesBanner.setSrc('http://thetvdb.com/banners/' + rec.get('SeriesBanner'));
                    return;
                }
            }

            seriesBanner.setSrc();
        }
    },

    onComboboxChange1: function(field, newValue, oldValue, eOpts) {
                var grid = Ext.ComponentQuery.query('#grid')[0];

                if (grid) {
                    grid.getStore().loadPage(1);
                }
    },

    onRadiogroupChange: function(field, newValue, oldValue, eOpts) {
                var grid = Ext.ComponentQuery.query('#grid')[0];

                if (grid) {
                    grid.getStore().loadPage(1);
                }
    }

});
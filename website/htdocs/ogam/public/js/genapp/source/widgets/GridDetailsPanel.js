/**
 * Licensed under EUPL v1.1 (see http://ec.europa.eu/idabc/eupl).
 *
 * © European Union, 2008-2012
 *
 * Reuse is authorised, provided the source is acknowledged. The reuse policy of the European Commission is implemented by a Decision of 12 December 2011.
 *
 * The general principle of reuse can be subject to conditions which may be specified in individual copyright notices.
 * Therefore users are advised to refer to the copyright notices of the individual websites maintained under Europa and of the individual documents.
 * Reuse is not applicable to documents subject to intellectual property rights of third parties.
 */

/**
 * The class of the Grid Details Panel.
 * 
 * @class Genapp.GridDetailsPanel
 * @extends Ext.GridPanel
 * @constructor Create a new GridDetailsPanel
 * @param {Object} config The config object
 */
Genapp.GridDetailsPanel = Ext.extend(Ext.grid.GridPanel, {
    /**
     * @cfg {Number} headerWidth
     * The tab header width. (Default to 60)
     */
    headerWidth:95,
    /**
     * @cfg {Boolean} closable
     * Panels themselves do not directly support being closed, but some Panel subclasses do (like
     * {@link Ext.Window}) or a Panel Class within an {@link Ext.TabPanel}.  Specify true
     * to enable closing in such situations. Defaults to true.
     */
    closable: true,
    /**
     * @cfg {Boolean} autoScroll
     * true to use overflow:'auto' on the panel's body element and show scroll bars automatically when
     * necessary, false to clip any overflowing content (defaults to true).
     */
    autoScroll:true,
    /**
     * @cfg {String} cls
     * An optional extra CSS class that will be added to this component's Element (defaults to 'genapp-query-grid-details-panel').
     * This can be useful for adding customized styles to the component or any of its children using standard CSS rules.
     */
    cls:'genapp-query-grid-details-panel',
    /**
     * @cfg {String} loadingMsg
     * The loading message (defaults to <tt>'Loading...'</tt>)
     */
    loadingMsg: 'Loading...',
    layout: 'fit',
    /**
     * @cfg {String} openDetailsButtonTitle
     * The open Details Button Title (defaults to <tt>'See the details'</tt>)
     */
    openDetailsButtonTitle: 'See the details',
    /**
     * @cfg {String} openDetailsButtonTip
     * The open Details Button Tip (defaults to <tt>'Display the row details into the details panel.'</tt>)
     */
    openDetailsButtonTip: 'Display the row details into the details panel.',
    /**
     * @cfg {String} getChildrenButtonTitle
     * The get Children Button Title (defaults to <tt>'Switch to the children'</tt>)
     */
    getChildrenButtonTitle: 'Switch to the children',
    /**
     * @cfg {String} getChildrenButtonTip
     * The get Children Button Tip (defaults to <tt>'Display the children of the data.'</tt>)
     */
    getChildrenButtonTip: 'Display the children of the data.',
    /**
     * @cfg {String} getParentButtonTitle
     * The get Parent Button Title (defaults to <tt>'Return to the parent'</tt>)
     */
    getParentButtonTitle: 'Return to the parent',
    /**
     * @cfg {String} getParentButtonTip
     * The get Parent Button Tip (defaults to <tt>'Display the parent of the data.'</tt>)
     */
    getParentButtonTip: 'Display the parent of the data.',
    /**
     * @cfg {Number} tipDefaultWidth
     * The tip Default Width. (Default to 300)
     */
    tipDefaultWidth: 300,
    sm: new Ext.grid.RowSelectionModel({singleSelect:true}),
    /**
     * @cfg {String} dateFormat The date format for the date
     *      fields (defaults to <tt>'Y/m/d'</tt>)
     */
    // TODO: Merge this param with the dateFormat param of the consultation panel
    dateFormat : 'Y/m/d',
    /**
     * @cfg {Number} tipImageDefaultWidth The tip Image Default Width.
     *      (Default to 200)
     */
    // TODO: Merge this param with the tipImageDefaultWidth param of the consultation panel
    tipImageDefaultWidth : 200,

    /**
     * Renders for the left tools column cell
     * 
     * @param {Object}
     *            value The data value for the cell.
     * @param {Object}
     *            metadata An object in which you may set the
     *            following attributes: {String} css A CSS class
     *            name to add to the cell's TD element. {String}
     *            attr : An HTML attribute definition string to
     *            apply to the data container element within the
     *            table cell (e.g. 'style="color:red;"').
     * @param {Ext.data.record}
     *            record The {@link Ext.data.Record} from which
     *            the data was extracted.
     * @param {Number}
     *            rowIndex Row index
     * @param {Number}
     *            colIndex Column index
     * @param {Ext.data.Store}
     *            store The {@link Ext.data.Store} object from
     *            which the Record was extracted.
     * @return {String} The html code for the column
     * @hide
     */
    renderLeftTools : function(value, metadata, record,
            rowIndex, colIndex, store) {

        var stringFormat = '';
        if (!this.hideDetails) {
            stringFormat = '<div class="genapp-query-grid-details-panel-slip" '
                +'onclick="Genapp.cardPanel.consultationPage.openDetails(\'{0}\', \'ajaxgetdetails\');"'
                +'ext:qtitle="' + this.openDetailsButtonTitle + '"'
                +'ext:qwidth="' + this.tipDefaultWidth + '"'
                +'ext:qtip="' + this.openDetailsButtonTip + '"'
            +'></div>';
        }
        if(this.hasChild) {
            stringFormat += '<div class="genapp-query-grid-details-panel-get-children" '
                +'onclick="Genapp.cardPanel.consultationPage.getChildren(\'{1}\',\'{0}\');"'
                +'ext:qtitle="' + this.getChildrenButtonTitle + '"'
                +'ext:qwidth="' + this.tipDefaultWidth + '"'
                +'ext:qtip="' + this.getChildrenButtonTip + '"'
            +'></div>';
        }
        return String.format(stringFormat, record.data.id, this.ownerCt.getId(),record.data.LOCATION_COMPL_DATA__SIT_NO_CLASS);
    },

    /**
     * Return the pattern used to format a number.
     * 
     * @param {String}
     *            decimalSeparator the decimal separator
     *            (default to',')
     * @param {Integer}
     *            decimalPrecision the decimal precision
     * @param {String}
     *            groupingSymbol the grouping separator (absent
     *            by default)
     */
    // TODO: Merge this function with the numberPattern fct of the consultation panel
    numberPattern : function(decimalSeparator, decimalPrecision, groupingSymbol) {
        // Building the number format pattern for use by ExtJS
        // Ext.util.Format.number
        var pattern = [], i;
        pattern.push('0');
        if (groupingSymbol) {
            pattern.push(groupingSymbol + '000');
        }
        if (decimalPrecision) {
            pattern.push(decimalSeparator);
            for (i = 0; i < decimalPrecision; i++) {
                pattern.push('0');
            }
        }
        return pattern.join('');
    },

    /**
     * Render an Icon for the data grid.
     */
     // TODO: Merge this function with the renderIcon fct of the consultation panel
    renderIcon : function(value, metadata, record, rowIndex, colIndex, store, columnLabel) {
        if (!Ext.isEmpty(value)) {
            return '<img src="' + Genapp.base_url + '/js/genapp/resources/images/picture.png"'
            + 'ext:qtitle="' + columnLabel + ' :"'
            + 'ext:qwidth="' + this.tipImageDefaultWidth + '"'
            + 'ext:qtip="'
            + Genapp.util.htmlStringFormat('<img width="' + (this.tipImageDefaultWidth - 12) 
            + '" src="' + Genapp.base_url + '/img/photos/' + value 
            +'" />') 
            + '">';
        }
    },

    // private
    initComponent : function() {
            this.itemId = this.initConf.id;
            this.hasChild = this.initConf.hasChild;
            this.title = this.initConf.title;
            this.parentItemId = this.initConf.parentItemId;
            // We need of the ownerCt here (before it's set automatically when this Component is added to a Container)
            this.ownerCt = this.initConf.ownerCt;

            this.store = new Ext.data.ArrayStore({
                // store configs
                autoDestroy: true,
                // reader configs
                idIndex: 0,
                fields: this.initConf.fields,
                data: this.initConf.data
            });

            // setup the columns
            var i;
            var columns = this.initConf.columns;
            for(i = 0; i<columns.length; i++){
                columns[i].header =  Genapp.util.htmlStringFormat(columns[i].header);
                columns[i].tooltip =  Genapp.util.htmlStringFormat(columns[i].tooltip);
                // TODO: Merge this part with the same part of the consultation panel
                switch (columns[i].type) {
                // TODO : BOOLEAN, CODE, COORDINATE, ARRAY,
                // TREE
                case 'STRING':
                case 'INTEGER':
                    columns[i].xtype = 'gridcolumn';
                    break;
                case 'NUMERIC':
                    columns[i].xtype = 'numbercolumn';
                    if (!Ext.isEmpty(columns[i].decimals)) {
                        columns[i].format = this.numberPattern('.', columns[i].decimals);
                    }
                    break;
                case 'DATE':
                    columns[i].xtype = 'datecolumn';
                    columns[i].format = this.dateFormat;
                    break;
                case 'IMAGE':
                    columns[i].header = '';
                    columns[i].width = 30;
                    columns[i].sortable = false;
                    columns[i].renderer = this.renderIcon.createDelegate(this, [Genapp.util.htmlStringFormat(columns[i].tooltip)], true);
                    break;
                default:
                    columns[i].xtype = 'gridcolumn';
                    break;
                }
            }
            var leftToolsHeader = '';
            if (!Ext.isEmpty(this.parentItemId)) {
                leftToolsHeader = '<div class="genapp-query-grid-details-panel-get-parent" '
                    +'onclick="Genapp.cardPanel.consultationPage.getParent(\'' + this.ownerCt.getId() +'\');"'
                    +'ext:qtitle="' + this.getParentButtonTitle + '"'
                    +'ext:qwidth="' + this.tipDefaultWidth + '"'
                    +'ext:qtip="' + this.getParentButtonTip + '"'
                    +'></div>';
            }
            this.initConf.columns.unshift({
                dataIndex:'leftTools',
                header:leftToolsHeader,
                renderer:this.renderLeftTools.createDelegate(this),
                sortable:false,
                fixed:true,
                menuDisabled:true,
                align:'center',
                width:50// 70 for three buttons
            });
            this.colModel = new Ext.grid.ColumnModel({
                defaults: {
                    width: 100,
                    sortable: true
                },
                columns: columns
            });
        Genapp.GridDetailsPanel.superclass.initComponent.call(this);
    }
});
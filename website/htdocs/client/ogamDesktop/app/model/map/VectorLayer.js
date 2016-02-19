/**
 * This class defines the model for the vector layers.
 *
 * TODO : Merge this file with the layer model file
 */
Ext.define('OgamDesktop.model.map.VectorLayer',{
	extend: 'Ext.data.Model',
	fields: [
		{name : 'code',type: 'string'},
        {name : 'label', type: 'string'},
        {name : 'url', type: 'string'},
        {name : 'url_wms', type: 'string'}
	]
});
/**
 * The results grid data store.
 */
Ext.define('OgamDesktop.store.result.Grid',{
	extend: 'Ext.data.Store',
	// The model is dynamically changed.
	model: 'OgamDesktop.model.result.Grid',
	remoteSort: true,
	proxy: {
		type: 'ajax',
		url: Ext.manifest.OgamDesktop.requestServiceUrl +'ajaxgetresultrows',
		
		// Usefull because default value of 'read' is 'GET' and the server searches 'POST' params :
		actionMethods: {create: 'POST', read: 'POST', update: 'POST', destroy: 'POST'},
		reader: {
			type: 'json',
			rootProperty: 'rows',
			totalProperty: 'total'
		}
	}
});
/**
 * Simple taxref picker class.
 * 
 * @class Genapp.form.picker.TaxrefPicker
 * @extends Genapp.form.picker.TreePicker
 * @constructor Create a new TaxrefPicker
 * @param {Object}
 *            config The config object
 * @xtype taxrefpicker
 */
Ext.namespace('Genapp.form.picker');

Genapp.form.picker.TaxrefPicker = Ext.extend(Genapp.form.picker.TreePicker, {

	/**
	 * Initialise the component.
	 */
	initComponent : function() {
		Genapp.form.picker.TaxrefPicker.superclass.initComponent.call(this);
		this.root.listeners = {
			'load' : {
				fn : this.italicFct,
				scope : this,
				single : true
			},
			'dblclick' : {// Select the node on double click
				fn : function(node, event) {
					this.fireEvent('select', node);
				}
			},
			scope : this
		};
	},

	/**
	 * Set this node item in italic if the taxon is a reference.
	 */
	italicFct : function(node) {
		for ( var i = 0; i < node.childNodes.length; i++) {
			var child = node.childNodes[i];
			if (!Ext.isEmpty(child.attributes) && child.attributes.isReference !== 1) {
				child.attributes.cls = 'genapp-taxref-picker-reference-node';
			} else {
				child.attributes.cls = 'genapp-taxref-picker-synonym-node';
			}
			child.addListener('load', this.italicFct, this, {
				single : true
			});
		}
	}
});
Ext.reg('taxrefpicker', Genapp.form.picker.TaxrefPicker);
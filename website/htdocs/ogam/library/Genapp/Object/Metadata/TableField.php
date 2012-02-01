<?php
/**
 * © French National Forest Inventory
 * Licensed under EUPL v1.1 (see http://ec.europa.eu/idabc/eupl).
 */

/**
 * Represent a Field of a Database.
 *
 * @package objects
 * @SuppressWarnings checkUnusedVariables
 */
class Genapp_Object_Metadata_TableField extends Genapp_Object_Metadata_Field {

	/**
	 * The physical name of the column.
	 * @var String
	 */
	var $columnName;

	/**
	 * Indicate if the field is calculated during an INSERT or UPDATE.
	 * @var bool
	 */
	var $isCalculated;


	/**
	 * Indicate if the field is editable.
	 * @var bool
	 */
	var $isEditable;

	/**
	 * Indicate if the field is insertable.
	 * @var bool
	 */
	var $isInsertable;

	/**
	 * These fields are only filled when the table field is of unit GEOM.
	 */
	var $xmin;
	var $xmax;
	var $ymin;
	var $ymax;
	var $center;

	/**
	 * The position of the table field to be displayed. This is used in the detail panel and in the data edition module.
	 */
	var $position;

	/**
	 * Clone the field
	 */
	function __clone() {
		foreach ($this as $name => $value) {
			if (gettype($value) == 'object') {
				$this->$name = clone ($this->$name);
			}
		}
	}

}

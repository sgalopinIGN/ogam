<?php
namespace OGAMBundle\Entity\Generic;

use OGAMBundle\Entity\Metadata\Field;

/**
 * A generic geom field is a generic field with some additional information
 */
class GenericGeomField extends GenericField {

	/**
	 * The bounding box of the value (geometry).
	 *
	 * @var BoundingBox
	 */
	private $valueBoundingBox;

	/**
	 * Return the bounding box of the value.
	 *
	 * @return the BoundingBox
	 */
	public function getValueBoundingBox() {
		return $this->valueBoundingBox;
	}

	/**
	 * Set the bounding box of the value.
	 *
	 * @param BoundingBox $valueBoundingBox        	
	 */
	public function setValueBoundingBox(BoundingBox $valueBoundingBox) {
		$this->valueBoundingBox = $valueBoundingBox;
		return $this;
	}
}

<?php
namespace Ubiquity\annotations;

/**
 * Annotation Column.
 * usages :
 * - column("columnName")
 * - column("name"=>"columnName")
 * - column("name"=>"columnName","nullable"=>true)
 * - column("name"=>"columnName","dbType"=>"typeInDb")
 *
 * @author jc
 * @version 1.0.3
 */
class ColumnAnnotation extends BaseAnnotation {

	public $name;

	public $nullable = false;

	public $dbType;

	/**
	 * Initialize the annotation.
	 */
	public function initAnnotation(array $properties) {
		if (isset($properties[0])) {
			$this->name = $properties[0];
			unset($properties[0]);
		} else if (isset($properties['name'])) {
			$this->name = $properties['name'];
		} else {
			throw new \Exception('Table annotation must have a name');
		}
		parent::initAnnotation($properties);
	}
}

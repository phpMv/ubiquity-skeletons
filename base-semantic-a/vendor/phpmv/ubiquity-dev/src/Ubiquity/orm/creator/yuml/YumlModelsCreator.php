<?php

namespace Ubiquity\orm\creator\yuml;

use Ubiquity\orm\creator\ModelsCreator;

/**
 * Generates models classes from Yuml.
 * Ubiquity\orm\creator\yuml$YumlModelsCreator
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.1
 * @package ubiquity.dev
 *
 */
class YumlModelsCreator extends ModelsCreator {
	/**
	 *
	 * @var YumlParser
	 */
	private $yumlParser;

	protected function init($config, $offset = 'default') {
		parent::init ( $config, $offset );
	}

	public function initYuml($yumlString) {
		$this->yumlParser = new YumlParser ( $yumlString );
	}

	protected function getTablesName() {
		return $this->yumlParser->getTableNames ();
	}

	protected function getFieldsInfos($tableName) {
		$fieldsInfos = array ();
		$fields = $this->yumlParser->getFields ( $tableName );
		foreach ( $fields as $field ) {
			$fieldsInfos [$field ['name']] = [ "Type" => $field ['type'],"Nullable" => (isset ( $field ["null"] ) && $field ["null"]) ];
		}
		return $fieldsInfos;
	}

	protected function getPrimaryKeys($tableName) {
		return $this->yumlParser->getPrimaryKeys ( $tableName );
	}

	protected function getForeignKeys($tableName, $pkName,$dbName=null) {
		return $this->yumlParser->getForeignKeys ( $tableName );
	}
}

<?php

namespace Ubiquity\db;

use Ubiquity\utils\base\UArray;
use Ubiquity\orm\OrmUtils;

/**
 * Utilitaires SQL
 * @author jc
 * @version 1.0.0.4
 */
class SqlUtils {
	
	public static $quote='`';

	private static function getParameters($keyAndValues) {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=":" . $key;
		}
		return $ret;
	}

	private static function getQuotedKeys($keyAndValues) {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=self::$quote . $key . self::$quote;
		}
		return $ret;
	}

	public static function getWhere($keyAndValues) {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=self::$quote . $key . self::$quote . "= :" . $key;
		}
		return implode(" AND ", $ret);
	}

	public static function getMultiWhere($values, $field) {
		$ret=array ();
		foreach ( $values as $value ) {
			$ret[]=self::$quote . $field . self::$quote . "='" . $value . "'";
		}
		return implode(" OR ", $ret);
	}

	public static function getInsertFields($keyAndValues) {
		return implode(",", self::getQuotedKeys($keyAndValues));
	}

	public static function getInsertFieldsValues($keyAndValues) {
		return implode(",", self::getParameters($keyAndValues));
	}

	public static function getUpdateFieldsKeyAndValues($keyAndValues) {
		$ret=array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret[]=self::$quote . $key . self::$quote . "= :" . $key;
		}
		return implode(",", $ret);
	}

	public static function checkWhere($condition){
		$c=\strtolower($condition);
		if ($condition != '' && \strstr($c, " join ")===false){
			$condition=" WHERE " . $condition;
		}
		return $condition;
	}

	public static function getCondition($keyValues,$classname=NULL,$separator=" AND ") {
		$retArray=array ();
		if (is_array($keyValues)) {
			if(!UArray::isAssociative($keyValues)){
				if(isset($classname)){
					$keys=OrmUtils::getKeyFields($classname);
					$keyValues=\array_combine($keys, $keyValues);
				}
			}
			foreach ( $keyValues as $key => $value ) {
				$retArray[]=self::$quote . $key . self::$quote . " = '" . $value . "'";
			}
			$condition=implode($separator, $retArray);
		} else
			$condition=$keyValues;
		return $condition;
	}

	public static function getFieldList($fields,$tableName=false){
		if(!\is_array($fields)){
			return $fields;
		}
		$result=[];
		$prefix="";
		if($tableName)
			$prefix=self::$quote.$tableName.self::$quote.".";
		foreach ($fields as $field) {
			$result[]= $prefix.self::$quote.$field.self::$quote;
		}
		return \implode(",", $result);
	}
}

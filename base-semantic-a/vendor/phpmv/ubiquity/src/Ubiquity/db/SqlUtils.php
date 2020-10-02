<?php

namespace Ubiquity\db;

use Ubiquity\utils\base\UArray;
use Ubiquity\orm\OrmUtils;

/**
 * SQL utilities
 *
 * @author jc
 * @version 1.0.2
 */
class SqlUtils {
	public static $quote = '`';

	private static function getParameters($keyAndValues) {
		$ret = array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret [] = ':' . $key;
		}
		return $ret;
	}

	private static function getQuotedKeys($keyAndValues) {
		$ret = array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret [] = self::$quote . $key . self::$quote;
		}
		return $ret;
	}

	public static function getWhere($keyAndValues) {
		$ret = array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret [] = self::$quote . $key . self::$quote . '= :' . $key;
		}
		return \implode ( ' AND ', $ret );
	}

	public static function getMultiWhere($values, $field) {
		$ret = array ();
		foreach ( $values as $value ) {
			$ret [] = self::$quote . $field . self::$quote . "='" . $value . "'";
		}
		return \implode ( ' OR ', $ret );
	}

	public static function getSearchWhere($likeOp, $fields, $value, $jokerBefore = '%', $jokerAfter = '%') {
		$ret = array ();
		foreach ( $fields as $field ) {
			$ret [] = self::$quote . $field . self::$quote . $likeOp . $jokerBefore . $value . $jokerAfter;
		}
		return \implode ( ' OR ', $ret );
	}

	public static function getInsertFields($keyAndValues) {
		return \implode ( ',', self::getQuotedKeys ( $keyAndValues ) );
	}

	public static function getInsertFieldsValues($keyAndValues) {
		return \implode ( ',', self::getParameters ( $keyAndValues ) );
	}

	public static function getUpdateFieldsKeyAndParams($keyAndValues) {
		$ret = array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret [] = self::$quote . $key . self::$quote . '= :' . $key;
		}
		return \implode ( ',', $ret );
	}

	public static function getUpdateFieldsKeyAndValues($keyAndValues) {
		$ret = array ();
		foreach ( $keyAndValues as $key => $value ) {
			$ret [] = self::$quote . $key . self::$quote . '= :' . $key;
		}
		return \implode ( ',', $ret );
	}

	public static function checkWhere($condition) {
		$c = \strtolower ( $condition );
		if ($condition != '' && \strstr ( $c, ' join ' ) === false) {
			$condition = ' WHERE ' . $condition;
		}
		return $condition;
	}

	public static function getCondition($keyValues, $classname = NULL, $separator = ' AND ') {
		if (! \is_array ( $keyValues )) {
			return $keyValues;
		} else {
			if (! UArray::isAssociative ( $keyValues )) {
				if (isset ( $classname )) {
					$keys = OrmUtils::getKeyFields ( $classname );
					if (\is_array ( $keys )) {
						$keyValues = \array_combine ( $keys, $keyValues );
					}
				}
			}
			$retArray = array ();
			foreach ( $keyValues as $key => $value ) {
				$retArray [] = self::$quote . $key . self::$quote . " = '" . $value . "'";
			}
			return \implode ( $separator, $retArray );
		}
	}

	/**
	 *
	 * @param array|string $fields
	 * @param boolean|string $tableName
	 * @return string
	 */
	public static function getFieldList($fields, $tableName = false) {
		if (! \is_array ( $fields )) {
			return $fields;
		}
		$result = [ ];
		$prefix = '';
		if (\is_string ( $tableName )) {
			$prefix = self::$quote . $tableName . self::$quote . '.';
		}
		foreach ( $fields as $field ) {
			$result [] = $prefix . self::$quote . $field . self::$quote;
		}
		return \implode ( ',', $result );
	}
}

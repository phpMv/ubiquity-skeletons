<?php

namespace Ubiquity\orm;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UArray;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ubiquity\orm\traits\OrmUtilsRelationsTrait;
use Ubiquity\orm\traits\OrmUtilsFieldsTrait;

/**
 * Object/relational mapping utilities
 *
 * @author jc
 * @version 1.0.3
 */
class OrmUtils {

	use OrmUtilsFieldsTrait,OrmUtilsRelationsTrait;
	private static $modelsMetadatas;

	public static function getModelMetadata($className) {
		if (! isset ( self::$modelsMetadatas [$className] )) {
			self::$modelsMetadatas [$className] = CacheManager::getOrmModelCache ( $className );
		}
		return self::$modelsMetadatas [$className];
	}

	public static function isSerializable($class, $member) {
		return ! self::_is ( $class, $member, "#notSerializable" );
	}

	public static function isNullable($class, $member) {
		return self::_is ( $class, $member, "#nullable" );
	}

	protected static function _is($class, $member, $like) {
		$ret = self::getAnnotationInfo ( $class, $like );
		if ($ret !== false) {
			return \array_search ( $member, $ret ) !== false;
		}
		return false;
	}

	public static function getFieldName($class, $member) {
		$ret = self::getAnnotationInfo ( $class, "#fieldNames" );
		if ($ret === false || ! isset ( $ret [$member] )) {
			return $member;
		}
		return $ret [$member];
	}

	public static function getTableName($class) {
		if (isset ( self::getModelMetadata ( $class ) ["#tableName"] ))
			return self::getModelMetadata ( $class ) ["#tableName"];
	}

	public static function getKeyFieldsAndValues($instance) {
		$class = get_class ( $instance );
		$kf = self::getAnnotationInfo ( $class, "#primaryKeys" );
		return self::getFieldsAndValues_ ( $instance, $kf );
	}

	public static function getFieldsAndValues_($instance, $members) {
		$ret = [ ];
		foreach ( $members as $member ) {
			$v = Reflexion::getMemberValue ( $instance, $member );
			$ret [$member] = $v;
		}
		return $ret;
	}

	public static function getKeyPropsAndValues_($instance, $props) {
		$ret = [ ];
		foreach ( $props as $prop ) {
			$v = Reflexion::getPropValue ( $instance, $prop );
			$ret [$prop->getName ()] = $v;
		}
		return $ret;
	}

	public static function getMembers($className) {
		$fieldNames = self::getAnnotationInfo ( $className, "#fieldNames" );
		if ($fieldNames !== false)
			return \array_keys ( $fieldNames );
		return [ ];
	}

	public static function getMembersAndValues($instance, $members = NULL) {
		$ret = array ();
		$className = get_class ( $instance );
		if (is_null ( $members ))
			$members = self::getMembers ( $className );
		foreach ( $members as $member ) {
			if (self::isSerializable ( $className, $member )) {
				$v = Reflexion::getMemberValue ( $instance, $member );
				if (self::isNotNullOrNullAccepted ( $v, $className, $member )) {
					$name = self::getFieldName ( $className, $member );
					$ret [$name] = $v;
				}
			}
		}
		return $ret;
	}

	public static function isNotNullOrNullAccepted($v, $className, $member) {
		$notNull = UString::isNotNull ( $v );
		return ($notNull) || (! $notNull && self::isNullable ( $className, $member ));
	}

	public static function getFirstKeyValue($instance) {
		$prop = OrmUtils::getFirstPropKey ( get_class ( $instance ) );
		return Reflexion::getPropValue ( $instance, $prop );
	}

	public static function getFirstKeyValue_($instance, $members) {
		$fkv = self::getFieldsAndValues_ ( $instance, $members );
		return \current ( $fkv );
	}

	public static function getKeyValues($instance) {
		$fkv = self::getKeyFieldsAndValues ( $instance );
		return implode ( "_", $fkv );
	}

	public static function getPropKeyValues($instance, $props) {
		$values = [ ];
		foreach ( $props as $prop ) {
			$values [] = Reflexion::getPropValue ( $instance, $prop );
		}
		return implode ( "_", $values );
	}

	public static function getMembersWithAnnotation($class, $annotation) {
		if (isset ( self::getModelMetadata ( $class ) [$annotation] ))
			return self::getModelMetadata ( $class ) [$annotation];
		return [ ];
	}

	/**
	 *
	 * @param object $instance
	 * @param string $memberKey
	 * @param array $array
	 * @return boolean
	 */
	public static function exists($instance, $memberKey, $array) {
		$accessor = "get" . ucfirst ( $memberKey );
		if (method_exists ( $instance, $accessor )) {
			foreach ( $array as $value ) {
				if ($value->$accessor () == $instance->$accessor ())
					return true;
			}
		}
		return false;
	}

	public static function getAnnotationInfo($class, $keyAnnotation) {
		$metas = self::getModelMetadata ( $class );
		if (isset ( $metas [$keyAnnotation] ))
			return $metas [$keyAnnotation];
		return false;
	}

	public static function getAnnotationInfoMember($class, $keyAnnotation, $member) {
		$info = self::getAnnotationInfo ( $class, $keyAnnotation );
		if ($info !== false) {
			if (UArray::isAssociative ( $info )) {
				if (isset ( $info [$member] )) {
					return $info [$member];
				}
			} else {
				if (\array_search ( $member, $info ) !== false) {
					return $member;
				}
			}
		}
		return false;
	}

	public static function setFieldToMemberNames(&$fields, $relFields) {
		foreach ( $fields as $index => $field ) {
			if (isset ( $relFields [$field] )) {
				$fields [$index] = $relFields [$field];
			}
		}
	}

	public static function objectAsJSON($instance) {
		$formatter = new ResponseFormatter ();
		$datas = $formatter->cleanRestObject ( $instance );
		return $formatter->format ( [ "pk" => self::getFirstKeyValue ( $instance ),"object" => $datas ] );
	}

	public static function getTransformers($class) {
		if (isset ( self::getModelMetadata ( $class ) ["#transformers"] ))
			return self::getModelMetadata ( $class ) ["#transformers"];
	}

	public static function getAccessors($class) {
		if (isset ( self::getModelMetadata ( $class ) ["#accessors"] ))
			return self::getModelMetadata ( $class ) ["#accessors"];
	}

	public static function clearMetaDatas() {
		self::$modelsMetadatas = [ ];
	}
}

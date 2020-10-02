<?php

namespace Ubiquity\orm\parser;

use mindplay\annotations\Annotations;

trait ReflexionFieldsTrait {

	abstract public static function getAnnotationMember($class, $member, $annotation);

	/**
	 *
	 * @param string $class
	 * @param string $member
	 * @return \Ubiquity\annotations\ColumnAnnotation|boolean
	 */
	protected static function getAnnotationColumnMember($class, $member) {
		return self::getAnnotationMember ( $class, $member, "@column" );
	}

	public static function getDbType($class, $member) {
		$ret = self::getAnnotationColumnMember ( $class, $member );
		if (! $ret)
			return false;
		else
			return $ret->dbType;
	}

	public static function isSerializable($class, $member) {
		if (self::getAnnotationMember ( $class, $member, "@transient" ) !== false || self::getAnnotationMember ( $class, $member, "@manyToOne" ) !== false || self::getAnnotationMember ( $class, $member, "@manyToMany" ) !== false || self::getAnnotationMember ( $class, $member, "@oneToMany" ) !== false)
			return false;
		else
			return true;
	}

	public static function getFieldName($class, $member) {
		$ret = self::getAnnotationColumnMember ( $class, $member );
		if ($ret === false || ! isset ( $ret->name ))
			$ret = $member;
		else
			$ret = $ret->name;
		return $ret;
	}

	public static function isNullable($class, $member) {
		$ret = self::getAnnotationColumnMember ( $class, $member );
		if (! $ret)
			return false;
		else
			return $ret->nullable;
	}

	public static function getProperties($class) {
		$reflect = new \ReflectionClass ( $class );
		return $reflect->getProperties ();
	}

	public static function getProperty($instance, $member) {
		$reflect = new \ReflectionClass ( $instance );
		$prop = false;
		if ($reflect->hasProperty ( $member )) {
			$prop = $reflect->getProperty ( $member );
		}
		return $prop;
	}

	public static function getPropertyType($class, $property) {
		return self::getMetadata ( $class, $property, "@var", "type" );
	}

	public static function getMetadata($class, $property, $type, $name) {
		$a = Annotations::ofProperty ( $class, $property, $type );
		if (! count ( $a )) {
			return false;
		}
		return trim ( $a [0]->$name, ";" );
	}
}


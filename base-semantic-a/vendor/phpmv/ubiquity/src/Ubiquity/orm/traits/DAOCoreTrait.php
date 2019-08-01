<?php

namespace Ubiquity\orm\traits;

use Ubiquity\db\SqlUtils;
use Ubiquity\events\DAOEvents;
use Ubiquity\events\EventsManager;
use Ubiquity\log\Logger;
use Ubiquity\orm\OrmUtils;
use Ubiquity\orm\parser\ConditionParser;
use Ubiquity\orm\parser\Reflexion;

/**
 * Core Trait for DAO class.
 * Ubiquity\orm\traits$DAOCoreTrait
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.1.0
 *
 * @property array $db
 * @property boolean $useTransformers
 * @property string $transformerOp
 *
 */
trait DAOCoreTrait {
	protected static $accessors = [ ];
	protected static $fields = [ ];

	abstract protected static function _affectsRelationObjects($className, $classPropKey, $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache);

	abstract protected static function prepareManyToMany(&$ret, $instance, $member, $annot = null);

	abstract protected static function prepareManyToOne(&$ret, $instance, $value, $fkField, $annotationArray);

	abstract protected static function prepareOneToMany(&$ret, $instance, $member, $annot = null);

	abstract protected static function _initRelationFields($included, $metaDatas, &$invertedJoinColumns, &$oneToManyFields, &$manyToManyFields);

	abstract protected static function getIncludedForStep($included);

	abstract protected static function getDb($model);

	private static function _getOneToManyFromArray(&$ret, $array, $fkv, $elementAccessor, $prop) {
		foreach ( $array as $element ) {
			$elementRef = $element->$elementAccessor ();
			if (($elementRef == $fkv) || (is_object ( $elementRef ) && Reflexion::getPropValue ( $elementRef, $prop ) == $fkv)) {
				$ret [] = $element;
			}
		}
	}

	private static function getManyToManyFromArray($instance, $array, $class, $parser) {
		$ret = [ ];
		$continue = true;
		$accessorToMember = "get" . ucfirst ( $parser->getInversedBy () );
		$myPkAccessor = "get" . ucfirst ( $parser->getMyPk () );
		$pk = self::getFirstKeyValue_ ( $instance );

		if (sizeof ( $array ) > 0) {
			$continue = method_exists ( current ( $array ), $accessorToMember );
		}
		if ($continue) {
			foreach ( $array as $targetEntityInstance ) {
				$instances = $targetEntityInstance->$accessorToMember ();
				if (is_array ( $instances )) {
					foreach ( $instances as $inst ) {
						if ($inst->$myPkAccessor () == $pk)
							array_push ( $ret, $targetEntityInstance );
					}
				}
			}
		} else {
			Logger::warn ( "DAO", "L'accesseur au membre " . $parser->getInversedBy () . " est manquant pour " . $parser->getTargetEntity (), "ManyToMany" );
		}
		return $ret;
	}

	protected static function getClass_($instance) {
		if (is_object ( $instance )) {
			return get_class ( $instance );
		}
		return $instance [0];
	}

	protected static function getInstance_($instance) {
		if (is_object ( $instance )) {
			return $instance;
		}
		return $instance [0];
	}

	protected static function getValue_($instance, $member) {
		if (is_object ( $instance )) {
			return Reflexion::getMemberValue ( $instance, $member );
		}
		return $instance [1];
	}

	protected static function getFirstKeyValue_($instance) {
		if (is_object ( $instance )) {
			return OrmUtils::getFirstKeyValue ( $instance );
		}
		return $instance [1];
	}

	protected static function _getOne($className, ConditionParser $conditionParser, $included, $useCache) {
		$conditionParser->limitOne ();
		$included = self::getIncludedForStep ( $included );
		$object = null;
		$invertedJoinColumns = null;
		$oneToManyFields = null;
		$manyToManyFields = null;

		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ["#tableName"];
		$hasIncluded = $included || (\is_array ( $included ) && \sizeof ( $included ) > 0);
		if ($hasIncluded) {
			self::_initRelationFields ( $included, $metaDatas, $invertedJoinColumns, $oneToManyFields, $manyToManyFields );
		}
		$transformers = $metaDatas ["#transformers"] [self::$transformerOp] ?? [ ];
		$query = self::getDb ( $className )->prepareAndExecute ( $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );
		if ($query && \sizeof ( $query ) > 0) {
			$oneToManyQueries = [ ];
			$manyToOneQueries = [ ];
			$manyToManyParsers = [ ];
			$accessors = $metaDatas ["#accessors"];
			$object = self::loadObjectFromRow ( \current ( $query ), $className, $invertedJoinColumns, $manyToOneQueries, $oneToManyFields, $manyToManyFields, $oneToManyQueries, $manyToManyParsers, $accessors, $transformers );
			if ($hasIncluded) {
				self::_affectsRelationObjects ( $className, OrmUtils::getFirstPropKey ( $className ), $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, [ $object ], $included, $useCache );
			}
			EventsManager::trigger ( DAOEvents::GET_ONE, $object, $className );
		}
		return $object;
	}

	/**
	 *
	 * @param string $className
	 * @param ConditionParser $conditionParser
	 * @param boolean|array $included
	 * @param boolean|null $useCache
	 * @return array
	 */
	protected static function _getAll($className, ConditionParser $conditionParser, $included = true, $useCache = NULL) {
		$included = self::getIncludedForStep ( $included );
		$objects = array ();
		$invertedJoinColumns = null;
		$oneToManyFields = null;
		$manyToManyFields = null;

		$metaDatas = OrmUtils::getModelMetadata ( $className );
		$tableName = $metaDatas ["#tableName"];
		$hasIncluded = $included || (\is_array ( $included ) && \sizeof ( $included ) > 0);
		if ($hasIncluded) {
			self::_initRelationFields ( $included, $metaDatas, $invertedJoinColumns, $oneToManyFields, $manyToManyFields );
		}
		$transformers = $metaDatas ["#transformers"] [self::$transformerOp] ?? [ ];
		$query = self::getDb ( $className )->prepareAndExecute ( $tableName, SqlUtils::checkWhere ( $conditionParser->getCondition () ), self::getFieldList ( $tableName, $metaDatas ), $conditionParser->getParams (), $useCache );
		$oneToManyQueries = [ ];
		$manyToOneQueries = [ ];
		$manyToManyParsers = [ ];
		$propsKeys = OrmUtils::getPropKeys ( $className );
		$accessors = $metaDatas ["#accessors"];
		foreach ( $query as $row ) {
			$object = self::loadObjectFromRow ( $row, $className, $invertedJoinColumns, $manyToOneQueries, $oneToManyFields, $manyToManyFields, $oneToManyQueries, $manyToManyParsers, $accessors, $transformers );
			$key = OrmUtils::getPropKeyValues ( $object, $propsKeys );
			$objects [$key] = $object;
		}
		if ($hasIncluded) {
			self::_affectsRelationObjects ( $className, OrmUtils::getFirstPropKey ( $className ), $manyToOneQueries, $oneToManyQueries, $manyToManyParsers, $objects, $included, $useCache );
		}
		EventsManager::trigger ( DAOEvents::GET_ALL, $objects, $className );
		return $objects;
	}

	protected static function getFieldList($tableName, $metaDatas) {
		if (! isset ( self::$fields [$tableName] )) {
			$members = \array_diff ( $metaDatas ["#fieldNames"], $metaDatas ["#notSerializable"] );
			self::$fields = SqlUtils::getFieldList ( $members, $tableName );
		}
		return self::$fields;
	}

	/**
	 *
	 * @param array $row
	 * @param string $className
	 * @param array $invertedJoinColumns
	 * @param array $manyToOneQueries
	 * @param array $accessors
	 * @return object
	 */
	private static function loadObjectFromRow($row, $className, &$invertedJoinColumns, &$manyToOneQueries, &$oneToManyFields, &$manyToManyFields, &$oneToManyQueries, &$manyToManyParsers, &$accessors, &$transformers) {
		$o = new $className ();
		if (self::$useTransformers) {
			foreach ( $transformers as $field => $transformer ) {
				$transform = self::$transformerOp;
				$row [$field] = $transformer::$transform ( $row [$field] );
			}
		}
		foreach ( $row as $k => $v ) {
			if (isset ( $accessors [$k] )) {
				$accesseur = $accessors [$k];
				$o->$accesseur ( $v );
			}
			$o->_rest [$k] = $v;
			if (isset ( $invertedJoinColumns ) && isset ( $invertedJoinColumns [$k] )) {
				$fk = "_" . $k;
				$o->$fk = $v;
				self::prepareManyToOne ( $manyToOneQueries, $o, $v, $fk, $invertedJoinColumns [$k] );
			}
		}
		if (isset ( $oneToManyFields )) {
			foreach ( $oneToManyFields as $k => $annot ) {
				self::prepareOneToMany ( $oneToManyQueries, $o, $k, $annot );
			}
		}
		if (isset ( $manyToManyFields )) {
			foreach ( $manyToManyFields as $k => $annot ) {
				self::prepareManyToMany ( $manyToManyParsers, $o, $k, $annot );
			}
		}
		return $o;
	}

	private static function parseKey(&$keyValues, $className) {
		if (! is_array ( $keyValues )) {
			if (strrpos ( $keyValues, "=" ) === false && strrpos ( $keyValues, ">" ) === false && strrpos ( $keyValues, "<" ) === false) {
				$keyValues = "`" . OrmUtils::getFirstKey ( $className ) . "`='" . $keyValues . "'";
			}
		}
	}
}

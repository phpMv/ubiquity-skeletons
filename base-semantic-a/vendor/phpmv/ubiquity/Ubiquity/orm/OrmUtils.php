<?php

namespace Ubiquity\orm;

use Ubiquity\orm\parser\Reflexion;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\base\UArray;
use Ubiquity\controllers\rest\ResponseFormatter;
use Ubiquity\orm\parser\ManyToManyParser;

/**
 * Utilitaires de mappage Objet/relationnel
 * @author jc
 * @version 1.0.0.5
 */
class OrmUtils {
	private static $modelsMetadatas;

	public static function getModelMetadata($className) {
		if (!isset(self::$modelsMetadatas[$className])) {
			self::$modelsMetadatas[$className]=CacheManager::getOrmModelCache($className);
		}
		return self::$modelsMetadatas[$className];
	}

	public static function isSerializable($class, $member) {
		$ret=self::getAnnotationInfo($class, "#notSerializable");
		if ($ret !== false)
			return \array_search($member, $ret) === false;
		else
			return true;
	}

	public static function isNullable($class, $member) {
		$ret=self::getAnnotationInfo($class, "#nullable");
		if ($ret !== false)
			return \array_search($member, $ret) !== false;
		else
			return false;
	}

	public static function getFieldName($class, $member) {
		$ret=self::getAnnotationInfo($class, "#fieldNames");
		if ($ret === false)
			$ret=$member;
		else
			$ret=$ret[$member];
		return $ret;
	}

	public static function getFieldNames($model){
		$fields=self::getAnnotationInfo($model, "#fieldNames");
		$result=[];
		$serializables=self::getSerializableFields($model);
		foreach ($fields as $member=>$field){
			if(\array_search($member, $serializables)!==false)
				$result[$field]=$member;
		}
		return $result;
	}

	public static function getTableName($class) {
		if(isset(self::getModelMetadata($class)["#tableName"]))
		return self::getModelMetadata($class)["#tableName"];
	}
	
	public static function getJoinTables($class){
		$result=[];
		
		if(isset(self::getModelMetadata($class)["#joinTable"])){
			$jts=self::getModelMetadata($class)["#joinTable"];
			foreach ($jts as $jt){
				$result[]=$jt["name"];
			}
		}
		return $result;
	}
	
	public static function getAllJoinTables($models){
		$result=[];
		foreach ($models as $model){
			$result=array_merge($result,self::getJoinTables($model));
		}
		return $result;
	}

	public static function getKeyFieldsAndValues($instance) {
		$kf=self::getAnnotationInfo(get_class($instance), "#primaryKeys");
		return self::getMembersAndValues($instance, $kf);
	}

	public static function getKeyFields($instance) {
		if(!\is_string($instance)){
			$instance=\get_class($instance);
		}
		return self::getAnnotationInfo($instance, "#primaryKeys");
	}

	public static function getMembers($className) {
		$fieldNames=self::getAnnotationInfo($className, "#fieldNames");
		if ($fieldNames !== false)
			return \array_keys($fieldNames);
		return [ ];
	}

	public static function getFieldTypes($className) {
		$fieldTypes=self::getAnnotationInfo($className, "#fieldTypes");
		if ($fieldTypes !== false)
			return $fieldTypes;
		return [ ];
	}

	public static function getFieldType($className,$field){
		$types= self::getFieldTypes($className);
		if(isset($types[$field]))
			return $types[$field];
		return "int";
	}

	public static function getMembersAndValues($instance, $members=NULL) {
		$ret=array ();
		$className=get_class($instance);
		if (is_null($members))
			$members=self::getMembers($className);
		foreach ( $members as $member ) {
			if (OrmUtils::isSerializable($className, $member)) {
				$v=Reflexion::getMemberValue($instance, $member);
				if (self::isNotNullOrNullAccepted($v, $className, $member)) {
					$name=self::getFieldName($className, $member);
					$ret[$name]=$v;
				}
			}
		}
		return $ret;
	}

	public static function isNotNullOrNullAccepted($v, $className, $member) {
		$notNull=UString::isNotNull($v);
		return ($notNull) || (!$notNull && OrmUtils::isNullable($className, $member));
	}

	public static function getFirstKey($class) {
		$kf=self::getAnnotationInfo($class, "#primaryKeys");
		return \reset($kf);
	}

	public static function getFirstKeyValue($instance) {
		$fkv=self::getKeyFieldsAndValues($instance);
		return \reset($fkv);
	}
	
	public static function getKeyValues($instance) {
		$fkv=self::getKeyFieldsAndValues($instance);
		return implode("_",$fkv);
	}

	/**
	 *
	 * @param object $instance
	 * @return mixed[]
	 */
	public static function getManyToOneMembersAndValues($instance) {
		$ret=array ();
		$class=get_class($instance);
		$members=self::getAnnotationInfo($class, "#manyToOne");
		if ($members !== false) {
			foreach ( $members as $member ) {
				$memberAccessor="get" . ucfirst($member);
				if (method_exists($instance, $memberAccessor)) {
					$memberInstance=$instance->$memberAccessor();
					if (isset($memberInstance)) {
						$keyValues=self::getKeyFieldsAndValues($memberInstance);
						if (sizeof($keyValues) > 0) {
							$fkName=self::getJoinColumnName($class, $member);
							$ret[$fkName]=reset($keyValues);
						}
					}
				}
			}
		}
		return $ret;
	}

	public static function getMembersWithAnnotation($class, $annotation) {
		if (isset(self::getModelMetadata($class)[$annotation]))
			return self::getModelMetadata($class)[$annotation];
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
		$accessor="get" . ucfirst($memberKey);
		if (method_exists($instance, $accessor)) {
			if ($array !== null) {
				foreach ( $array as $value ) {
					if ($value->$accessor() == $instance->$accessor())
						return true;
				}
			}
		}
		return false;
	}

	public static function getJoinColumnName($class, $member) {
		$annot=self::getAnnotationInfoMember($class, "#joinColumn", $member);
		if ($annot !== false) {
			$fkName=$annot["name"];
		} else {
			$fkName="id" . ucfirst(self::getTableName(ucfirst($member)));
		}
		return $fkName;
	}

	public static function getAnnotationInfo($class, $keyAnnotation) {
		if (isset(self::getModelMetadata($class)[$keyAnnotation]))
			return self::getModelMetadata($class)[$keyAnnotation];
		return false;
	}

	public static function getAnnotationInfoMember($class, $keyAnnotation, $member) {
		$info=self::getAnnotationInfo($class, $keyAnnotation);
		if ($info !== false) {
			if(UArray::isAssociative($info)){
				if (isset($info[$member])) {
					return $info[$member];
				}
			}else{
				if(\array_search($member, $info)!==false){
					return $member;
				}
			}
		}
		return false;
	}

	public static function getSerializableFields($class) {
		$notSerializable=self::getAnnotationInfo($class, "#notSerializable");
		$fieldNames=\array_keys(self::getAnnotationInfo($class, "#fieldNames"));
		return \array_diff($fieldNames, $notSerializable);
	}

	public static function getFieldsInRelations($class) {
		$result=[ ];
		if ($manyToOne=self::getAnnotationInfo($class, "#manyToOne")) {
			$result=\array_merge($result, $manyToOne);
		}
		if ($oneToMany=self::getAnnotationInfo($class, "#oneToMany")) {
			$result=\array_merge($result, \array_keys($oneToMany));
		}
		if ($manyToMany=self::getAnnotationInfo($class, "#manyToMany")) {
			$result=\array_merge($result, \array_keys($manyToMany));
		}
		return $result;
	}
	
	public static function getAnnotFieldsInRelations($class) {
		$result=[ ];
		if ($manyToOnes=self::getAnnotationInfo($class, "#manyToOne")) {
			$joinColumns=self::getAnnotationInfo($class, "#joinColumn");
			foreach ($manyToOnes as $manyToOne ){
				if(isset($joinColumns[$manyToOne])){
					$result[$manyToOne]=["type"=>"manyToOne","value"=>$joinColumns[$manyToOne]];
				}
			}
		}
		if ($oneToManys=self::getAnnotationInfo($class, "#oneToMany")) {
			foreach ($oneToManys as $field=>$oneToMany){
				$result[$field]=["type"=>"oneToMany","value"=>$oneToMany];
			}
		}
		if ($manyToManys=self::getAnnotationInfo($class, "#manyToMany")) {
			foreach ($manyToManys as $field=>$manyToMany){
				$result[$field]=["type"=>"manyToMany","value"=>$manyToMany];
			}
		}
		return $result;
	}
	
	public static function getUJoinSQL($model,$arrayAnnot,$field,&$aliases){
		$type=$arrayAnnot["type"];$annot=$arrayAnnot["value"];
		$table=self::getTableName($model);
		$tableAlias=(isset($aliases[$table]))?$aliases[$table]:$table;
		if($type==="manyToOne"){
			$fkClass=$annot["className"];
			$fkTable=OrmUtils::getTableName($fkClass);
			$fkField=$annot["name"];
			$pkField=self::getFirstKey($fkClass);
			$alias=self::getJoinAlias($table, $fkTable);
			$result="INNER JOIN `{$fkTable}` `{$alias}` ON `{$tableAlias}`.`{$fkField}`=`{$alias}`.`{$pkField}`";
		}elseif($type==="oneToMany"){
			$fkClass=$annot["className"];
			$fkAnnot=OrmUtils::getAnnotationInfoMember($fkClass, "#joinColumn", $annot["mappedBy"]);
			$fkTable=OrmUtils::getTableName($fkClass);
			$fkField=$fkAnnot["name"];
			$pkField=self::getFirstKey($model);
			$alias=self::getJoinAlias($table, $fkTable);
			$result="INNER JOIN `{$fkTable}` `{$alias}` ON `{$tableAlias}`.`{$pkField}`=`{$alias}`.`{$fkField}`";
		}else{
			$parser=new ManyToManyParser($model,$field);
			$parser->init($annot);
			$fkTable=$parser->getTargetEntityTable();
			$fkClass=$parser->getTargetEntityClass();
			$alias=self::getJoinAlias($table, $fkTable);
			$result=$parser->getSQL($alias,$aliases);
		}
		
		if(array_search($alias, $aliases)!==false){
			$result="";
		}
		$aliases[$fkTable]=$alias;
		return ["class"=>$fkClass,"table"=>$fkTable,"sql"=>$result,"alias"=>$alias];
	}
	
	private static function getJoinAlias($table,$fkTable){
		return uniqid($fkTable.'_'.$table[0]);
	}

	public static function getManyToOneFields($class) {
		return self::getAnnotationInfo($class, "#manyToOne");
	}

	public static function getManyToManyFields($class) {
		$result=self::getAnnotationInfo($class, "#manyToMany");
		if($result!==false)
			return \array_keys($result);
		return [];
	}

	public static function getDefaultFk($classname) {
		return "id" . \ucfirst(self::getTableName($classname));
	}

	public static function getMemberJoinColumns($instance,$member,$metaDatas=NULL){
		if(!isset($metaDatas)){
			$class=get_class($instance);
			$metaDatas=self::getModelMetadata($class);
		}
		$invertedJoinColumns=$metaDatas["#invertedJoinColumn"];
		foreach ($invertedJoinColumns as $field=>$invertedJoinColumn){
			if($invertedJoinColumn["member"]===$member){
				return [$field,$invertedJoinColumn];
			}
		}
		return null;
	}
	
	public static function objectAsJSON($instance){
		$formatter=new ResponseFormatter();
		$datas=$formatter->cleanRestObject($instance);
		return $formatter->format(["pk"=>self::getFirstKeyValue($instance),"object"=>$datas]);
	}
}

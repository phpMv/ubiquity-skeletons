<?php
namespace Ubiquity\orm\creator;

use Ubiquity\annotations\IdAnnotation;
use Ubiquity\annotations\ManyToOneAnnotation;
use Ubiquity\annotations\OneToManyAnnotation;
use Ubiquity\annotations\ManyToManyAnnotation;
use Ubiquity\annotations\JoinTableAnnotation;
use Ubiquity\annotations\JoinColumnAnnotation;
use Ubiquity\annotations\ColumnAnnotation;
use Ubiquity\contents\validation\ValidationModelGenerator;
use Ubiquity\annotations\TransformerAnnotation;

/**
 * Represents a data member in a model class.
 * Ubiquity\orm\creator$Member
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.2
 * @package ubiquity.dev
 *
 */
class Member {

	private $name;

	private $primary;

	private $manyToOne;

	private $annotations;

	private $access;

	public function __construct($name, $access = 'private') {
		$this->name = $name;
		$this->annotations = [];
		$this->primary = false;
		$this->manyToOne = false;
		$this->access = $access;
	}

	public function __toString() {
		$annotationsStr = "";
		if (sizeof($this->annotations) > 0) {
			$annotationsStr = "\n\t/**";
			$annotations = $this->annotations;
			\array_walk($annotations, function ($item) {
				return $item . "";
			});
			if (\sizeof($annotations) > 1) {
				$annotationsStr .= "\n\t * " . implode("\n\t * ", $annotations);
			} else {
				$annotationsStr .= "\n\t * " . \end($annotations);
			}
			$annotationsStr .= "\n\t**/";
		}
		return $annotationsStr . "\n\t{$this->access} $" . $this->name . ";\n";
	}

	public function setPrimary() {
		if ($this->primary === false) {
			$this->annotations[] = new IdAnnotation();
			$this->primary = true;
		}
	}

	public function setDbType($infos) {
		$annot = new ColumnAnnotation();
		$annot->name = $this->name;
		$annot->dbType = $infos["Type"];
		$annot->nullable = (\strtolower($infos["Nullable"]) === "yes");
		$this->annotations["column"] = $annot;
	}

	public function addManyToOne($name, $className, $nullable = false) {
		$this->annotations[] = new ManyToOneAnnotation();
		$joinColumn = new JoinColumnAnnotation();
		$joinColumn->name = $name;
		$joinColumn->className = $className;
		$joinColumn->nullable = $nullable;
		$this->annotations[] = $joinColumn;
		$this->manyToOne = true;
	}

	public function addOneToMany($mappedBy, $className) {
		$oneToMany = new OneToManyAnnotation();
		$oneToMany->mappedBy = $mappedBy;
		$oneToMany->className = $className;
		$this->annotations[] = $oneToMany;
	}

	private function addTransformer($name) {
		$transformer = new TransformerAnnotation();
		$transformer->name = $name;
		$this->annotations[] = $transformer;
	}

	/**
	 * Try to set a transformer to the member.
	 */
	public function setTransformer() {
		if ($this->isPassword()) {
			$this->addTransformer('password');
		} else {
			$dbType = $this->getDbType();
			if ($dbType == 'datetime') {
				$this->addTransformer('datetime');
			}
		}
	}

	public function isPassword() {
		// Array of multiple translations of the word "password" which could be taken as name of the table field in database
		$pwArray = array(
			'password',
			'senha',
			'lozinka',
			'heslotajne',
			'helslo_tajne',
			'wachtwoord',
			'contrasena',
			'salasana',
			'motdepasse',
			'mot_de_passe',
			'passwort',
			'passord',
			'haslo',
			'senha',
			'parola',
			'naponb',
			'contrasena',
			'loesenord',
			'losenord',
			'sifre',
			'naponb',
			'matkhau',
			'mat_khau'
		);
		return \in_array($this->name, $pwArray);
	}

	public function addManyToMany($targetEntity, $inversedBy, $joinTable, $joinColumns = [], $inverseJoinColumns = []) {
		$manyToMany = new ManyToManyAnnotation();
		$manyToMany->targetEntity = $targetEntity;
		$manyToMany->inversedBy = $inversedBy;
		$jt = new JoinTableAnnotation();
		$jt->name = $joinTable;
		if (\sizeof($joinColumns) == 2) {
			$jt->joinColumns = $joinColumns;
		}
		if (\sizeof($inverseJoinColumns) == 2) {
			$jt->inverseJoinColumns = $inverseJoinColumns;
		}
		$this->annotations[] = $manyToMany;
		$this->annotations[] = $jt;
	}

	public function getName() {
		return $this->name;
	}

	public function isManyToOne() {
		return $this->manyToOne;
	}

	public function getManyToOne() {
		foreach ($this->annotations as $annotation) {
			if ($annotation instanceof JoinColumnAnnotation) {
				return $annotation;
			}
		}
		return null;
	}

	public function isPrimary() {
		return $this->primary;
	}

	public function getGetter() {
		$result = "\n\t public function get" . \ucfirst($this->name) . "(){\n";
		$result .= "\t\t" . 'return $this->' . $this->name . ";\n";
		$result .= "\t}\n";
		return $result;
	}

	public function getSetter() {
		$result = "\n\t public function set" . \ucfirst($this->name) . '($' . $this->name . "){\n";
		$result .= "\t\t" . '$this->' . $this->name . '=$' . $this->name . ";\n";
		$result .= "\t}\n";
		return $result;
	}

	public function hasAnnotations() {
		return \count($this->annotations) > 1;
	}

	public function isNullable() {
		if (isset($this->annotations["column"]))
			return $this->annotations["column"]->nullable;
		return false;
	}

	public function getDbType() {
		if (isset($this->annotations["column"]))
			return $this->annotations["column"]->dbType;
		return "mixed";
	}

	public function addValidators() {
		$parser = new ValidationModelGenerator($this->getDbType(), $this->name, ! $this->isNullable(), $this->primary);
		$validators = $parser->parse();
		if (sizeof($validators)) {
			$this->annotations = array_merge($this->annotations, $validators);
		}
	}

	public function getAnnotations() {
		return $this->annotations;
	}
}

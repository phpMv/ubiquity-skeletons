<?php
namespace Ubiquity\annotations\router;

/**
 * Defines a route with the `options` method
 * Ubiquity\annotations\router$PostAnnotation
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 *
 */
class OptionsAnnotation extends RouteAnnotation {

	public function initAnnotation(array $properties) {
		parent::initAnnotation($properties);
		$this->methods = [
			"options"
		];
	}
}


<?php

namespace Ubiquity\exceptions;

/**
 * Exceptions for ThemesManager.
 * Ubiquity\exceptions$ThemesException
 * This class is part of Ubiquity
 *
 * @author jcheron <myaddressmail@gmail.com>
 * @version 1.0.0
 * @since Ubiquity 2.1.0
 *
 */
class ThemesException extends UbiquityException {

	public function __construct($message = null, $code = null, $previous = null) {
		parent::__construct ( $message, $code, $previous );
	}
}

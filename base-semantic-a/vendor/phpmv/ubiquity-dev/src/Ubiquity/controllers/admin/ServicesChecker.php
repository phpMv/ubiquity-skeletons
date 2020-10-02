<?php
namespace Ubiquity\controllers\admin;

/**
 * Ubiquity\controllers\admin$ServicesChecker
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class ServicesChecker {

	public static function hasSecurity(): bool {
		return \class_exists('\\Ubiquity\\security\\csrf\\CsrfManager');
	}

	public static function hasShieldon(): bool {
		return \class_exists('\\Shieldon\\Container');
	}

	public static function isCsrfStarted(): bool {
		if (self::hasSecurity()) {
			return \Ubiquity\security\csrf\CsrfManager::isStarted();
		}
		return false;
	}

	public static function isEncryptionStarted(): bool {
		if (self::hasSecurity()) {
			return \Ubiquity\security\data\EncryptionManager::isStarted();
		}
		return false;
	}

	public static function isShieldonStarted(): bool {
		if (self::hasShieldon()) {
			return \Shieldon\Container::get('firewall') !== null;
		}
		return false;
	}
}


<?php
namespace controllers;

/**
 * Controller Shieldon
 */
class %controllerName% extends \Ubiquity\controllers\Controller {

	/**
	 * %route%
	 */
	public function index() {
		// Get Firewall instance from Shieldon Container.
		$firewall = \Shieldon\Container::get('firewall');

		// Get into the Firewall Panel.
		$controlPanel = new \Shieldon\FirewallPanel($firewall);
		%csrf%
		$controlPanel->entry();
	}
}

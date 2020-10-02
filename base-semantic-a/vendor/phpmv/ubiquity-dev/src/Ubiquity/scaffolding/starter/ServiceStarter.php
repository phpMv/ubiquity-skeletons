<?php
namespace Ubiquity\scaffolding\starter;

use Ubiquity\utils\base\UFileSystem;

/**
 * Ubiquity\scaffolding\starter$ServiceStarter
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class ServiceStarter {

	private $servicesContent;

	private $servicesName;

	private function loadServices() {
		$file = $this->getServicesFilename();
		if (\file_exists($file)) {
			$this->servicesContent = UFileSystem::load($file);
		}
	}

	private function getServicesFilename() {
		return \ROOT . \DS . 'config' . \DS . $this->servicesName . '.php';
	}

	public function __construct(?string $servicesName = 'services') {
		$this->servicesName = $servicesName;
		$this->loadServices();
	}

	public function getTemplateDir() {
		return \dirname(__DIR__) . "/templates/services/";
	}

	public function addService($serviceName) {
		$file = $this->getTemplateDir() . $serviceName . '.tpl';
		if (\file_exists($file)) {
			$serviceContent = UFileSystem::load($file);
			if (\strpos($this->servicesContent, $serviceContent) === false) {
				$this->servicesContent = rtrim($this->servicesContent) . "\n" . $serviceContent;
			}
		}
	}

	public function addServices(array $services) {
		foreach ($services as $service) {
			$this->addService($service);
		}
	}

	public function save() {
		return UFileSystem::save($this->getServicesFilename(), $this->servicesContent);
	}
}


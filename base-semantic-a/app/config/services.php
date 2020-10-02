<?php
use Ubiquity\controllers\Router;
use Ubiquity\events\EventsManager;
use Ubiquity\events\RestEvents;
use Ubiquity\controllers\rest\RestBaseController;

\Ubiquity\cache\CacheManager::startProd($config);
\Ubiquity\orm\DAO::start();
Router::startAll();
Router::addRoute("_default", "controllers\\IndexController");
\Ubiquity\assets\AssetsManager::start($config);
EventsManager::addListener(RestEvents::BEFORE_INSERT, function ($o, array &$datas, RestBaseController $resource) {
	unset($datas['aliases']);
});

<?php
use Ubiquity\controllers\Router;

//\Ubiquity\orm\DAO::start(); to use only with multiple databases
Router::start();
Router::addRoute("_default", "controllers\\IndexController");
\Ubiquity\assets\AssetsManager::start($config);

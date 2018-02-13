<?php
namespace Ubiquity\controllers\admin\traits;

use Ajax\JsUtils;
use Ubiquity\utils\StrUtils;
use Ubiquity\controllers\admin\popo\ControllerAction;
use Ubiquity\controllers\Router;
use Ubiquity\cache\CacheManager;
use Ubiquity\controllers\admin\popo\Route;
use Ubiquity\controllers\Startup;

/**
 * @author jc
 * @property JsUtils $jquery
 */
trait RoutesTrait{
	abstract public function _getAdminData();
	abstract public function _getAdminViewer();
	abstract public function _getAdminFiles();
	abstract protected function addNavigationTesting();
	abstract protected function showSimpleMessage($content,$type,$icon="info",$timeout=NULL,$staticName=null);

	public function initCacheRouter(){
		$config=Startup::getConfig();
		\ob_start();
		CacheManager::initCache($config,"controllers");
		$message=\ob_get_clean();
		echo $this->showSimpleMessage(\nl2br($message), "info","info",4000);
		$routes=CacheManager::getRoutes();
		echo $this->_getAdminViewer()->getRoutesDataTable(Route::init($routes));
		echo $this->jquery->compile($this->view);
	}

	public function filterRoutes(){
		$filter=$_POST["filter"];
		$ctrls=[];
		if(StrUtils::isNotNull($filter)){
			$filter=\trim($_POST["filter"]);
			$ctrls=ControllerAction::initWithPath($filter);
			$routes=Router::filterRoutes($filter);
		}
		else $routes=CacheManager::getRoutes();
		echo $this->_getAdminViewer()->getRoutesDataTable(Route::init($routes));
		if(\sizeof($ctrls)>0){
			echo $this->_getAdminViewer()->getControllersDataTable($ctrls);
		}
		$this->addNavigationTesting();
		echo $this->jquery->compile($this->view);
	}
}

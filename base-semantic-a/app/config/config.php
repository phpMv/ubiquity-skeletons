<?php
return array(
		"siteUrl"=>"http://127.0.0.1/{projectName}/",
		"database"=>[
				"type"=>"mysql",
				"dbName"=>"",
				"serverName"=>"127.0.0.1",
				"port"=>"3306",
				"user"=>"root",
				"password"=>"",
				"options"=>[],
				"cache"=>false
		],
		"sessionName"=>"{projectName}",
		"namespaces"=>[],
		"templateEngine"=>'Ubiquity\\views\\engine\\Twig',
		"templateEngineOptions"=>array("cache"=>false),
		"test"=>false,
		"debug"=>false,
		"logger"=>function(){return new \Ubiquity\log\libraries\UMonolog("{projectName}",\Monolog\Logger::INFO);},
		"di"=>array(
				"@exec"=>array(
						"jquery"=>function ($controller){
							return \Ubiquity\core\Framework::diSemantic($controller);
						}
						)
				),
		"cache"=>["directory"=>"cache/","system"=>"Ubiquity\\cache\\system\\ArrayCache","params"=>[]],
		"mvcNS"=>["models"=>"models","controllers"=>"controllers","rest"=>""],
		"isRest"=>function(){
			return \Ubiquity\utils\http\URequest::getUrlParts()[0]==="rest";
		}
);

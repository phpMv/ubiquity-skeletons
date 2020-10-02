<?php
namespace Ubiquity\devtools\cmd;

use Ubiquity\utils\base\UIntrospection;
use Ubiquity\utils\base\UFileSystem;

/**
 * Define a command complete desciption.
 * Ubiquity\devtools\cmd$Command
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class Command {

	protected $name;

	protected $description;

	protected $value;

	protected $aliases;

	protected $parameters;

	protected $examples;

	protected $category;

	protected static $customCommands;

	protected static $customAliases;

	public function __construct($name = '', $value = '', $description = '', $aliases = [], $parameters = [], $examples = [], $category = 'custom') {
		$this->name = $name;
		$this->value = $value;
		$this->description = $description;
		$this->aliases = $aliases;
		$this->parameters = $parameters;
		$this->examples = $examples;
		$this->category = $category;
	}

	public function simpleString() {
		return "\t" . $this->name . " [" . $this->value . "]\t\t" . $this->description;
	}

	public function longString() {
		$dec = "\t";
		$result = "\n<b>■ " . $this->name . "</b> [" . ConsoleFormatter::colorize($this->value, ConsoleFormatter::YELLOW) . "] =>";
		$result .= "\n" . $dec . "· " . $this->description;
		if (sizeof($this->aliases) > 0) {
			$result .= "\n" . $dec . "· Aliases :";
			$aliases = $this->aliases;
			array_walk($aliases, function (&$alias) {
				$alias = "<b>" . $alias . "</b>";
			});
			$result .= " " . implode(",", $aliases);
		}
		if (sizeof($this->parameters) > 0) {
			$result .= "\n" . $dec . "· Parameters :";
			foreach ($this->parameters as $param => $content) {
				$result .= "\n" . $dec . "\t<b>-" . $param . "</b>";
				$result .= $content . "\n";
			}
		}
		if (sizeof($this->examples) > 0) {
			$result .= "\n" . $dec . "<b>× Samples :</b>";
			foreach ($this->examples as $desc => $sample) {
				if (is_string($desc)) {
					$result .= "\n" . $dec . "\t" . ConsoleFormatter::colorize($desc, ConsoleFormatter::LIGHT_GRAY);
				}
				$result .= "\n" . $dec . "\t  · " . ConsoleFormatter::colorize($sample, ConsoleFormatter::CYAN);
			}
		}
		return $result;
	}

	public static function getInfo($cmd) {
		$commands = self::getCommands();
		$result = [];
		if ($cmd != null) {
			foreach ($commands as $command) {
				if ($command->getName() == $cmd) {
					return [
						[
							"info" => "Command <b>{$cmd}</b> find by name",
							"cmd" => $command
						]
					];
				} elseif (array_search($cmd, $command->getAliases()) !== false) {
					$result[] = [
						"info" => "Command <b>{$cmd}</b> find by alias",
						"cmd" => $command
					];
				} elseif (stripos($command->getDescription(), $cmd) !== false) {
					$result[] = [
						"info" => "Command <b>{$cmd}</b> find in description",
						"cmd" => $command
					];
				} else {
					$parameters = $command->getParameters();
					foreach ($parameters as $parameter) {
						if ($cmd == $parameter->getName()) {
							$result[] = [
								"info" => "Command <b>{$cmd}</b> find by the name of a parameter",
								"cmd" => $command
							];
						}
						if (stripos($parameter->getDescription(), $cmd) !== false) {
							$result[] = [
								"info" => "Command <b>{$cmd}</b> find in parameter description",
								"cmd" => $command
							];
						}
					}
				}
			}
		}
		return $result;
	}

	public static function project() {
		return new Command("project", "projectName", "Creates a new #ubiquity project.", [
			"new",
			"create-project"
		], [
			"b" => Parameter::create("dbName", "Sets the database name.", []),
			"s" => Parameter::create("serverName", "Defines the db server address.", [], "127.0.0.1"),
			"p" => Parameter::create("port", "Defines the db server port.", [], "3306"),
			"u" => Parameter::create("user", "Defines the db server user.", [], "root"),
			"w" => Parameter::create("password", "Defines the db server password.", [], ""),
			"h" => Parameter::create("themes", "Install themes.", [
				"semantic",
				"bootstrap",
				"foundation"
			], ""),
			"m" => Parameter::create("all-models", "Creates all models from database.", [], ""),
			"a" => Parameter::create("admin", "Adds UbiquityMyAdmin tool.", [
				"true",
				"false"
			], "false"),
			"i" => Parameter::create("siteUrl", "Sets the site base URL.", []),
			"e" => Parameter::create("rewriteBase", "Sets .htaccess file rewriteBase.", [])
		], [
			'Creates a new project' => 'Ubiquity new blog',
			'With admin interface' => 'Ubiquity new blog -a',
			'and models generation' => 'Ubiquity new blog -a -m -b=blogDB'
		], 'installation');
	}

	public static function controller() {
		return new Command("controller", "controllerName", "Creates a new controller.", [
			"create-controller"
		], [
			"v" => Parameter::create("views", "creates an associated view folder", [
				"true",
				"false"
			], 'false')
		], [
			'Creates a controller' => 'Ubiquity controller UserController',
			'with its associated view' => 'Ubiquity controller UserController -v'
		], 'controllers');
	}

	public static function model() {
		return new Command("model", "tableName", "Generates a new model.", [
			"create-model"
		], [
			'd' => Parameter::create('database', 'The database connection to use', [], 'default'),
			'a' => Parameter::create('access', 'The default access to the class members', [], 'private')
		], [
			'Ubiquity model User',
			'Ubiquity model Author -d=projects',
			'Ubiquity model Author -d=projects -a=protected'
		], 'models');
	}

	public static function routes() {
		return new Command("info:routes", "", "Display the cached routes.", [
			"info:r",
			"info::routes"
		], [
			"t" => Parameter::create("type", "Defines the type of routes to display.", [
				"all",
				"routes",
				"rest"
			]),
			"l" => Parameter::create("limit", " Specifies the number of routes to return.", []),
			"o" => Parameter::create("offset", "Specifies the number of routes to skip before starting to return.", []),
			"s" => Parameter::create("search", "Search routes corresponding to a path.", []),
			"m" => Parameter::create("method", "Allows to specify a method with search attribute.", [
				'get',
				'post',
				'put',
				'delete',
				'patch'
			])
		], [
			'All routes' => 'Ubiquity info:routes',
			'Rest routes' => 'Ubiquity info:routes -type=rest',
			'Only the routes with the method post' => 'Ubiquity info:routes -type=rest -m=-post'
		], 'router');
	}

	public static function version() {
		return new Command("version", "", "Return PHP, Framework and dev-tools versions.", [], [], [], 'system');
	}

	public static function allModels() {
		return new Command("all-models", "", "Generates all models from database.", [
			"create-all-models"
		], [
			'd' => Parameter::create('database', 'The database connection to use (offset)', [], 'default'),
			'a' => Parameter::create('access', 'The default access to the class members', [], 'private')
		], [
			'Ubiquity all-models',
			'Ubiquity all-models -d=projects',
			'Ubiquity all-models -d=projects -a=protected'
		], 'models');
	}

	public static function clearCache() {
		return new Command("clear-cache", "", "Clear models cache.", [], [
			"t" => Parameter::create("type", "Defines the type of cache to reset.", [
				"all",
				"annotations",
				"controllers",
				"rest",
				"models",
				"queries",
				"views"
			], 'all')
		], [
			'Clear all caches' => 'Ubiquity clear-cache -t=all',
			'Clear models cache' => 'Ubiquity clear-cache -t=models'
		], 'cache');
	}

	public static function initCache() {
		return new Command("init-cache", "", "Init the cache for models, router, rest.", [], [
			"t" => Parameter::create("type", "Defines the type of cache to create.", [
				"all",
				"controllers",
				"rest",
				"models"
			], 'all')
		], [
			'Init all caches' => 'Ubiquity init-cache',
			'Init models cache' => 'Ubiquity init-cache -t=models'
		], 'cache');
	}

	public static function serve() {
		return new Command("serve", "", "Start a web server.", [], [
			"h" => Parameter::create("host", "Sets the host ip address.", [], '127.0.0.1'),
			"p" => Parameter::create("port", "Sets the listen port number.", [], 8090),
			"t" => Parameter::create("type", "Sets the server type.", [
				'php',
				'react',
				'swoole',
				'roadrunner'
			], 'php')
		], [
			'Starts a php server at 127.0.0.1:8090' => 'Ubiquity serve',
			'Starts a reactPHP server at 127.0.0.1:8080' => 'Ubiquity serve -t=react'
		], 'servers');
	}

	public static function selfUpdate() {
		return new Command("self-update", "", "Updates Ubiquity framework for the current project.", [], [], [], 'installation');
	}

	public static function admin() {
		return new Command("admin", "", "Add UbiquityMyAdmin webtools to the current project.", [], [], [], 'installation');
	}

	public static function help() {
		return new Command("help", "?", "Get some help about a dev-tools command.", [], [], [
			'Get some help about crud' => 'Ubiquity help crud'
		], 'system');
	}

	public static function crudController() {
		return new Command("crud", "crudControllerName", "Creates a new CRUD controller.", [
			"crud-controller"
		], [
			"r" => Parameter::create("resource", "The model used", []),
			"d" => Parameter::create("datas", "The associated Datas class", [
				"true",
				"false"
			], "true"),
			"v" => Parameter::create("viewer", "The associated Viewer class", [
				"true",
				"false"
			], "true"),
			"e" => Parameter::create("events", "The associated Events class", [
				"true",
				"false"
			], "true"),
			"t" => Parameter::create("templates", "The templates to modify", [
				"index",
				"form",
				"display"
			], "index,form,display"),
			"p" => Parameter::create("path", "The associated route", [])
		], [
			'Creates a crud controller for the class models\User' => 'Ubiquity crud CrudUsers -r=User',
			'and associates a route to it' => 'Ubiquity crud CrudUsers -r=User -p=/users',
			'allows customization of index and form templates' => 'Ubiquity crud CrudUsers -r=User -t=index,form',
			'Creates a crud controller for the class models\projects\Author' => 'Ubiquity crud Authors -r=models\projects\Author'
		], 'controllers');
	}

	public static function restController() {
		return new Command("rest", "restControllerName", "Creates a new REST controller.", [
			"rest-controller"
		], [
			"r" => Parameter::create("resource", "The model used", []),
			"p" => Parameter::create("path", "The associated route", [])
		], [
			'Creates a REST controller for the class models\User' => 'Ubiquity rest RestUsers -r=User -p=/rest/users'
		], 'rest');
	}

	public static function restApiController() {
		return new Command("restapi", "restControllerName", "Creates a new REST API controller.", [
			"restapi-controller"
		], [
			"p" => Parameter::create("path", "The associated route", [])
		], [
			'Creates a REST API controller' => 'Ubiquity restapi -p=/rest'
		], 'rest');
	}

	public static function dao() {
		return new Command("dao", "command", "Executes a DAO command (getAll,getOne,count,uGetAll,uGetOne,uCount).", [
			"DAO"
		], [
			"r" => Parameter::create("resource", "The model used", []),
			"c" => Parameter::create("condition", "The where part of the query", []),
			"i" => Parameter::create("included", "The associated members to load (boolean or array: client.*,commands)", []),
			"p" => Parameter::create("parameters", "The parameters for a parameterized query", []),
			"f" => Parameter::create("fields", "The fields to display in the response", [])
		], [
			'Returns all instances of models\User' => 'Ubiquity dao getAll -r=User',
			'Returns all instances of models\User and includes their commands' => 'Ubiquity dao getAll -r=User -i=commands',
			'Returns the User with the id 5' => 'Ubiquity dao getOne -c="id=5"-r=User',
			'Returns the list of users belonging to the "Brittany" or "Normandy" regions' => 'Ubiquity uGetAll -r=User -c="region.name= ? or region.name= ?" -p=Brittany,Normandy'
		], 'models');
	}

	public static function authController() {
		return new Command("auth", "authControllerName", "Creates a new controller for authentification.", [
			"auth-controller"
		], [
			"e" => Parameter::create("extends", "The base class of the controller (must derived from AuthController)", [], "Ubiquity\\controllers\\auth\\AuthController"),
			"t" => Parameter::create("templates", "The templates to modify", [
				"index",
				"info",
				"noAccess",
				"disconnected",
				"message",
				"baseTemplate"
			], 'index,info,noAccess,disconnected,message,baseTemplate'),
			"p" => Parameter::create("path", "The associated route", [])
		], [
			'Creates a new controller for authentification' => 'Ubiquity auth AdminAuthController',
			'and associates a route to it' => 'Ubiquity auth AdminAuthController -p=/admin/auth',
			'allows customization of index and info templates' => 'Ubiquity auth AdminAuthController -t=index,info'
		], 'controllers');
	}

	public static function newAction() {
		return new Command("action", "controller.action", "Creates a new action in a controller.", [
			"new-action"
		], [
			"p" => Parameter::create("params", "The action parameters (or arguments)", []),
			"r" => Parameter::create("route", "The associated route path", []),
			"v" => Parameter::create("create-view", "Creates the associated view", [
				"true",
				"false"
			], "false")
		], [
			'Adds the action all in controller Users' => 'Ubiquity action Users.all',
			'Adds the action display in controller Users with a parameter' => 'Ubiquity action Users.display -p=idUser',
			'and associates a route to it' => 'Ubiquity action Users.display -p=idUser -r=/users/display/{idUser}',
			'with multiple parameters' => 'Ubiquity action Users.search -p=name,address',
			'and create the associated view' => 'Ubiquity action Users.search -p=name,address -v'
		], 'controllers');
	}

	public static function infoModel() {
		return new Command("info:model", "?infoType", "Returns the model meta datas.", [
			"info-model"
		], [
			"s" => Parameter::create("separate", "If true, returns each info in a separate table", [
				"true",
				"false"
			], "false"),
			"m" => Parameter::create("model", "The model on which the information is sought.", []),
			"f" => Parameter::create("fields", "The fields to display in the table.", [])
		], [
			'Gets metadatas for User class' => 'Ubiquity info:model -m=User'
		], 'models');
	}

	public static function infoModels() {
		return new Command("info:models", "", "Returns the models meta datas.", [
			"info-models"
		], [
			'd' => Parameter::create('database', 'The database connection to use (offset)', [], 'default'),
			"m" => Parameter::create("models", "The models on which the information is sought.", []),
			"f" => Parameter::create("fields", "The fields to display in the table.", [])
		], [
			'Gets metadatas for all models in default db' => 'Ubiquity info:models',
			'Gets metadatas for all models in messagerie db' => 'Ubiquity info:models -d=messagerie',
			'Gets metadatas for User and Group models' => 'Ubiquity info:models -m=User,Group',
			'Gets all primary keys for all models' => 'Ubiquity info:models -f=#primaryKeys'
		], 'models');
	}

	public static function infoValidation() {
		return new Command("info:validation", "?memberName", "Returns the models validation info.", [
			"info-validation",
			"info:validators",
			"info-validators"
		], [
			"s" => Parameter::create("separate", "If true, returns each info in a separate table", [
				'true',
				'false'
			], 'false'),
			"m" => Parameter::create("model", "The model on which the information is sought.", [])
		], [
			'Gets validators for User class' => 'Ubiquity info:validation -m=User',
			'Gets validators for User class on member firstname' => 'Ubiquity info:validation firstname -m=User'
		], 'models');
	}

	public static function configInfo() {
		return new Command("config", "", "Returns the config informations from app/config/config.php.", [
			"info-config",
			"info:config"
		], [
			"f" => Parameter::create("fields", "The fields to display.", [])
		], [
			'Display all config vars' => 'Ubiquity config',
			'Display database config vars' => 'Ubiquity config -f=database'
		], 'system');
	}

	public static function configSet() {
		return new Command("config:set", "", "Modify/add variables and save them in app/config/config.php. Supports only long parameters with --.", [
			"info-set",
			"set:config",
			"set-config"
		], [], [
			'Assigns a new value to siteURL' => 'Ubiquity config:set --siteURL=http://127.0.0.1/quick-start/',
			'Change the database name and port' => 'Ubiquity config:set --database.dbName=blog --database.port=3307'
		], 'system');
	}

	public static function newTheme() {
		return new Command("create-theme", "themeName", "Creates a new theme or installs an existing one.", [
			"create:theme"
		], [
			"x" => Parameter::create("extend", "If specified, inherits from an existing theme (bootstrap,semantic or foundation).", [
				'bootstrap',
				'semantic',
				'foundation'
			])
		], [
			'Creates a new theme custom' => 'Ubiquity create-theme custom',
			'Creates a new theme inheriting from Bootstrap' => 'Ubiquity theme myBootstrap -x=bootstrap'
		], 'gui');
	}

	public static function installTheme() {
		return new Command("theme", "themeName", "Installs an existing theme or creates a new one if the specified theme does not exists.", [
			"install-theme",
			"install:theme"
		], [], [
			'Creates a new theme custom' => 'Ubiquity theme custom',
			'Install bootstrap theme' => 'Ubiquity theme bootstrap'
		], 'gui');
	}

	public static function bootstrap() {
		return new Command("bootstrap", "command", "Executes a command created in app/config/_bootstrap.php file for bootstraping the app.", [
			"boot"
		], [], [
			'Bootstrap for dev mode' => 'Ubiquity bootstrap dev',
			'Bootstrap for prod mode' => 'Ubiquity bootstrap prod'
		], 'servers');
	}

	public static function composer() {
		return new Command("composer", "command", "Executes a composer command.", [
			"compo"
		], [], [
			'composer update' => 'Ubiquity composer update',
			'composer update with no-dev' => 'Ubiquity composer nodev',
			'composer optimization for production' => 'Ubiquity composer optimize'
		], 'system');
	}

	public static function mailer() {
		return new Command("mailer", "part", "Displays mailer classes, mailer queue or mailer dequeue.", [], [], [
			'Display mailer classes' => 'Ubiquity mailer classes',
			'Display mailer messages in queue(To send)' => 'Ubiquity mailer queue',
			'Display mailer messages in dequeue(sent)' => 'Ubiquity mailer dequeue'
		], 'mailer');
	}

	public static function sendMails() {
		return new Command("sendMail", "", "Send message(s) from queue.", [
			"sendMails"
		], [
			"n" => Parameter::create("num", "If specified, Send the mail at the position n in queue.", [])
		], [
			'Send all messages to send from queue' => 'Ubiquity semdmails',
			'Send the first message in queue' => 'Ubiquity sendmail 1'
		], 'mailer');
	}

	public static function newMail() {
		return new Command("new-mail", "name", "Creates a new mailer class.", [
			"newMail",
			"new:mail"
		], [], [
			'Creates a new mailer class' => 'Ubiquity newMail InformationMail'
		], 'mailer');
	}

	public static function createCommand() {
		return new Command("create-command", "commandName", "Creates a new custom command for the devtools.", [
			"create:command",
			'createCommand'
		], [
			"v" => Parameter::create("value", "The command value (first parameter).", []),
			"p" => Parameter::create("parameters", "The command parameters (comma separated).", []),
			"d" => Parameter::create("description", "The command description.", []),
			"a" => Parameter::create("aliases", "The command aliases (comma separated).", [])
		], [
			'Creates a new custom command' => 'Ubiquity create-command custom'
		], 'system');
	}

	protected static function getCustomCommandInfos() {
		$result = [];
		$commands = self::getCustomCommands();
		if (is_array($commands)) {
			foreach ($commands as $o) {
				$result[] = $o->getCommand();
			}
		}
		return $result;
	}

	public static function getCustomCommands() {
		if (\class_exists(\Ubiquity\utils\base\UIntrospection::class)) {
			if (! isset(self::$customCommands)) {
				$classes = UIntrospection::getChildClasses('\\Ubiquity\\devtools\\cmd\\commands\\AbstractCustomCommand');
				foreach ($classes as $class) {
					$o = new $class();
					$cmd = $o->getCommand();
					self::$customCommands[$cmd->getName()] = $o;
					$aliases = $cmd->getAliases();
					if (is_array($aliases)) {
						foreach ($aliases as $alias) {
							self::$customAliases[$alias] = $o;
						}
					}
				}
			}
		}
		return self::$customCommands;
	}

	public static function reloadCustomCommands(array $config = []) {
		if (\class_exists(\Ubiquity\utils\base\UIntrospection::class)) {
			self::$customCommands = null;
			self::$customAliases = [];
			self::preloadCustomCommands($config);
			self::getCustomCommands();
		}
	}

	public static function getCustomAliases() {
		return self::$customAliases;
	}

	public static function preloadCustomCommands(array $config = []) {
		if (\class_exists(\Ubiquity\utils\base\UIntrospection::class)) {
			$config['cmd-pattern'] ??= 'commands' . \DS . '*.cmd.php';
			$files = UFileSystem::glob_recursive($config['cmd-pattern']);
			foreach ($files as $file) {
				include_once $file;
			}
		}
	}

	public static function getCommandNames(array $excludedCategories = [
		'installation' => false,
		'servers' => false
	], $excludedCommands = []) {
		$result = [];
		$commands = self::getCommands();
		foreach ($commands as $command) {
			$cat = $command->getCategory();
			$commandName = $command->getName();
			if (! isset($excludedCommands[$commandName]) && ! isset($excludedCategories[$cat])) {
				$result[] = $commandName;
			}
		}
		return $result;
	}

	public static function getCommands() {
		return [
			self::initCache(),
			self::clearCache(),
			self::controller(),
			self::newAction(),
			self::authController(),
			self::crudController(),
			self::newTheme(),
			self::installTheme(),

			self::project(),
			self::serve(),
			self::bootstrap(),
			self::help(),
			self::version(),
			self::model(),
			self::allModels(),
			self::dao(),
			self::selfUpdate(),
			self::composer(),
			self::admin(),
			self::restController(),
			self::restApiController(),
			self::routes(),
			self::infoModel(),
			self::infoModels(),
			self::infoValidation(),
			self::configInfo(),
			self::configSet(),
			self::mailer(),
			self::newMail(),
			self::sendMails(),
			self::createCommand(),
			...self::getCustomCommandInfos()
		];
	}

	/**
	 *
	 * @return mixed
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getValue() {
		if ($this->value != null) {
			return ltrim($this->value, '?');
		}
		return $this->value;
	}

	public function hasRequiredValue() {
		return $this->value != null && \substr($this->value, 0, 1) !== '?';
	}

	/**
	 *
	 * @return mixed
	 */
	public function getAliases() {
		return $this->aliases;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getParameters() {
		return $this->parameters;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getExamples() {
		return $this->examples;
	}

	/**
	 *
	 * @return mixed
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 *
	 * @param mixed $category
	 */
	public function setCategory($category) {
		$this->category = $category;
	}

	public function hasParameters() {
		return count($this->parameters) > 0;
	}

	public function hasValue() {
		return $this->value != null;
	}

	public function isImmediate() {
		return ! $this->hasParameters() && ! $this->hasValue();
	}

	public function __toString() {
		return $this->name;
	}
}

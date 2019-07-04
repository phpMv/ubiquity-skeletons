# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unrelease]
Nothing

## [2.2.0] - 2019-07-03
### Added
- Web-tools
  - Maintenance mode (see https://github.com/phpMv/ubiquity/issues/49)
  - Updates checking for caches
  - Customization (tools)

### Deleted/updated
- Webtools removed from Ubiquity main repository and are in there own repo

Use ``composer require phpmv/ubiquity-webtools`` to install them.

#### Breaking change possible:
Classes relocation
- ``Ubiquity\controllers\admin\utils\CodeUtils``->``Ubiquity\utils\base\CodeUtils``
- ``Ubiquity\controllers\admin\interfaces\HasModelViewerInterface``->``Ubiquity\controllers\crud\interfaces\HasModelViewerInterface``
- ``Ubiquity\controllers\admin\viewers\ModelViewer``->``Ubiquity\controllers\crud\viewers\ModelViewer``
- ``Ubiquity\controllers\admin\popo\CacheFile`` -> ``Ubiquity\cache\CacheFile``
- ``Ubiquity\controllers\admin\popo\ControllerSeo`` -> ``Ubiquity\seo\ControllerSeo``
- ``Ubiquity\controllers\admin\traits\UrlsTrait`` -> ``Ubiquity\controllers\crud\traits\UrlsTrait``
  
#### Migration 
- For new projects, update devtools: ``composer global update``
- In existing projects:
``composer require phpmv/ubiquity-webtools`` for webtools installation.

### Fixed
- Router: pb with route priority attribute see [#54](https://github.com/phpMv/ubiquity/issues/54)

## [2.1.4] - 2019-06-13
### Added
- `Translate` module in webtools
- `transChoice` method for translations with pluralization (`tc` in twig templates)
- Transactions and nested transactions in `Database` and `DAO` classes see [#42](https://github.com/phpMv/ubiquity/issues/42)
- `getById` method in `DAO` class (optimization)
- `Ubiquity-swoole` server (``Ubiquity serve --type=swoole``)
### Fixed
- Fatal error in startup (not 404) fix [#43](https://github.com/phpMv/ubiquity/issues/43)
- Version 2.1.3 displays the number of version 2.1.2

## [2.1.3] - 2019-05-09
### Added
- Support for Http methods customization (for URequest & Uresponse) via ``Ubiquity\utils\http\foundation\AbstractHttp`` class.
- Support for session customization via ``Ubiquity\utils\http\session\AbstractSession``
- multisites session ``Ubiquity\utils\http\session\MultisiteSession``(1.0.0-beta)
- ``ReactPHP`` server available from the devtools with ``Ubiquity serve -t=react`` command
### Fixed
- [ORM] model Table annotation : fix [#39](https://github.com/phpMv/ubiquity/issues/39)
### Fixed
- [Logging] init logger fails if debug=false : fix [#31](https://github.com/phpMv/ubiquity/issues/31)
### Documentation
- DAO [querying, updates](https://micro-framework.readthedocs.io/en/latest/model/dao.html#loading-data)
- In doc for di : fix [#41](https://github.com/phpMv/ubiquity/issues/41)

## [2.1.2] - 2019-04-27
### Fixed
- Twig views caching : fix https://github.com/phpMv/ubiquity/issues/26
- ORM : sync `$instance->_rest` array with `$instance` updates
- REST:
  - pb on adding in `SimpleRestController` : fix https://github.com/phpMv/ubiquity/issues/27
  - pb an update with manyToOne members : fix https://github.com/phpMv/ubiquity/issues/30

## [2.1.1] - 2019-04-19
### Added
- `Transformer` module see in [documentation](https://micro-framework.readthedocs.io/en/latest/contents/transformers.html)
- `SimpleRestController` + `SimpleApiRestController` classes for Rest part

### Changed
- `Translation` module use default cache system (ArrayCache) and no more APC (performances ++)

### Fixed
- webtools Rest section
  - `Authorization Bearer` pb in input field (no open issue)
  - `POST` request for adding an instance with `RestController` (no open issue)
- webtools Models section, CRUDControllers
  - Model adding or updating in modal form fail see https://github.com/phpMv/ubiquity/issues/25
- JsonAPI finalization
### Documentation
- REST module [rest doc](https://micro-framework.readthedocs.io/en/latest/rest/index.html#rest)
- Transformers module [Transformers doc](https://micro-framework.readthedocs.io/en/latest/contents/transformers.html#transformers)

## [2.1.0] - 2019-04-01
### Added
- Themes manager with bootstrap, Semantic-ui and foundation
  - `AssetsManager` for css,js, fonts and images integration
  - `ThemesManager` for css framework integration
  - Themes part in webtools interface
- Dependency injection annotations
  - `@injected` inject a member in a controller defined by a dependency in config
  - `@autowired` inject an instance of class defined by type with `@var` annotation
   
### Changed
- dependency injection mecanism
  - controller cache for di
  - `@exec`key in `config[di]` for injections at runtime

#### Breaking change possible:
  use `"di"=>["@exec"=>[your injections]] `instead of `"di"=>[your injections]`
  
### Fixed
- An exception is thrown In case of problem with the Database connection (in `DataBase::connect` method) see https://github.com/phpMv/ubiquity/issues/12
>The connection to the database must be protected by a `try/catch` in `app/config/services.php`
```
try{
	\Ubiquity\orm\DAO::startDatabase($config);
}catch(Exception $e){
	echo $e->getMessage();
}
```
### Documentation
- Dependency injection updates [di doc](https://micro-framework.readthedocs.io/en/latest/controller/di/index.html#di)
- Themes managment [Assets and themes doc](https://micro-framework.readthedocs.io/en/latest/view/index.html#assets)

## [2.0.11] - 2019-03-14
### Added
- Rest [JsonAPI](https://jsonapi.org/format/) implementation
  - ``JsonApiRestController`` class
- methods in ``UCookie``
  - ``exists``: Tests the existence of a cookie
  - ``setRaw``: Sends a raw cookie without urlencoding the cookie value
- method in ``UResponse``
  - ``enableCORS``: enables globaly CORS for a domain (this was possible before by using ``setAccessControl*`` methods)
  
### Changed
- method ``set`` in ``UCookie`` (parameters ``$secure`` & ``$httpOnly`` added)

### Fixed
- issue [pb with config variable in Twig views](https://github.com/phpMv/ubiquity/issues/7)
- deprecated ref to apcu in Translation ``ArrayLoader`` removed

## [2.0.10] - 2019-02-22
### Added
- Webtools
  - validation info in models part
- Acceptance, functionnal and unit tests (70% coverage)

### Changed
- Webtools
  - models metadatas presentation
- Documentation
- Restoration of Translation class
- Compatibility with devtools 1.1.5

## [2.0.9] - 2019-01-21
### Removed
- Usage of ``@`` (replaced with ``??`` operator)

## [2.0.8] - 2019-01-20
### Changed
- Optimizations
  - ORM & relations oneToMany
  - apc to apcu cache for Translations
  - Router : routes array minification
  - Scrutinizer debugging : 0 bug !
  - Scrutinizer evaluation : 9.61 very good!
  - Translator=>TranslatorManager with static methods

## [2.0.7] - 2019-01-11
### Changed
- Scrutinizer cleaning
### Added
- String validators

## [2.0.6] - 2018-12-29
Update for phpbenchmarks compatibility

## [2.0.5] - 2018-12-28
### Changed
- TranslatorManager
- ValidatorsManager
- NormalizersManager

## [2.0.4] - 2018-11-21
### Added
- UQL (Ubiquity Query Language)
- AuthControllers
- CRUDControllers

### Changed
- SQL Queries optimization (groupings)

## [2.0.3] - 2018-04-16
### Added
- Config file edition and checking
- @framework location for internal default views

## [2.0.2] - 2018-03-13
### Changed
- manyToMany annot bug fixed
- quote in SqlUtils

## [2.0.1] - 2018-03-11
### Added
- SEO controller for generating robots.txt and sitemap.xml files (webtools interface)
- Adding new utility classes
  - Ubiquity\utils\http\UResponse
  - Ubiquity\utils\http\UCookie
### Changed
- Renaming utility classes:
  - Ubiquity\utils\RequestUtils -> Ubiquity\utils\http\URequest
  - Ubiquity\utils\SessionUtils -> Ubiquity\utils\http\USession
  - Ubiquity\utils\StrUtils -> Ubiquity\utils\base\UString
  - Ubiquity\utils\JArray -> Ubiquity\utils\base\UArray
  - Ubiquity\utils\FsUtils -> Ubiquity\utils\base\UFileSystem
  - Ubiquity\utils\Introspection -> Ubiquity\utils\base\UIntrospection

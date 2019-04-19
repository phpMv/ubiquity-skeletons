<?php
include_once 'tests/acceptance/BaseAcceptance.php';
class AdminCest extends BaseAcceptance {

	public function _before(AcceptanceTester $I) {
		/*
		 * $I->amOnPage ( "/blank.html" );
		 * $I->setCookie ( 'PHPSESSID', 'el4ukv0kqbvoirg7nkp4dncpk3' );
		 */
	}

	// tests
	public function tryToGotoIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/" );
		$I->seeElement ( 'body' );
		$I->see ( 'Ubiquity', [ 'css' => 'body' ] );
	}

	// tests
	public function tryToGotoAdminIndex(AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$I->see ( 'Used to perform CRUD operations on data', [ 'css' => 'body' ] );
	}

	private function gotoAdminModule(string $url, AcceptanceTester $I) {
		$I->amOnPage ( "/Admin/index" );
		$I->seeInCurrentUrl ( "Admin/index" );
		$this->waitAndclick ( $I, "a[href='" . $url . "']" );
		$I->waitForElementVisible ( "#content-header", self::TIMEOUT );
		$I->canSeeInCurrentUrl ( $url );
	}

	public function tryToGotoAdminModels(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Models", $I );
		$I->click ( "a[data-model='models.Connection']" );
		$I->waitForElementVisible ( "#btAddNew", self::TIMEOUT );
		$I->canSeeInCurrentUrl ( "/Admin/showModel/models.Connection" );
		$I->see ( 'organizations/display/4', "#lv td" );
		$I->click ( "button._edit[data-ajax='8']" );
		$I->waitForElementVisible ( "#modal-frmEdit-models-Connection", self::TIMEOUT );
		$I->canSee ( 'Editing an existing object', 'form' );
	}

	// tests
	public function tryGotoAdminRoutes(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Routes", $I );
		$I->click ( "#bt-init-cache" );
		$I->waitForElementVisible ( "#divRoutes .ui.message.info", self::TIMEOUT );
		$I->canSee ( 'Router cache', '.ui.message.info' );
	}

	// tests
	public function tryGotoAdminControllers(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Controllers", $I );
		// Create a new controller
		$I->fillField ( "#frmCtrl [name='name']", 'TestAcceptanceController' );
		$I->click ( '#ck-lbl-ck-div-name' ); // Click on create associated view
		$I->click ( '#action-field-name' ); // Create the controller
		$I->waitForElementVisible ( "#msgGlobal", self::TIMEOUT );
		// Test controller creation
		$I->canSee ( 'TestAcceptanceController', '#msgGlobal' );
		$I->canSee ( 'controller has been created in', '#msgGlobal' );
		$I->canSee ( 'The default view associated has been created in', '#msgGlobal' );
		$I->click ( "#filter-bt" );
		$I->waitForElementVisible ( "#filtering-frm", self::TIMEOUT );
		$I->click ( "#cancel-btn" );
		$I->amOnPage ( "/TestAcceptanceController" );
		$I->canSeeInCurrentUrl ( "/TestAcceptanceController" );
	}

	// tests
	public function tryGotoAdminCache(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Cache", $I );
		$I->click ( "#ck-cacheTypes-4" );
		$I->waitForElement ( "#dd-type-Annotations", self::TIMEOUT );
	}

	// tests
	public function tryGotoAdminRest(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Rest", $I );
		$I->canSee ( "Restfull web service", "body" );
		$I->click ( "#bt-init-rest-cache" );
		$I->waitForText ( "Rest service", self::TIMEOUT, "body" );
		// Add a new resource
		$I->click ( "#bt-new-resource" );
		$I->waitForText ( "Creating a new REST controller...", self::TIMEOUT, "body" );
		$I->fillField ( "#ctrlName", "RestUsersController" );
		$I->fillField ( "#route", "/rest-users" );
		$I->click ( "#bt-create-new-resource" );
		$I->wait ( 10 );
		$I->click ( "#bt-init-rest-cache" );
		$I->wait ( 10 );
		$I->amOnPage ( "/rest-users/get/1" );
		$I->see ( '"count":101' );
		$I->amOnPage ( "/rest-users/getOne/1/false" );
		$I->see ( 'Benjamin' );
		$I->amOnPage ( "/rest-users/getOne/1/true" );
		$I->see ( 'Benjamin' );
		$I->see ( 'de Caen-Normandie' );
		$I->see ( 'Auditeurs' );
		$I->see ( 'myaddressmail@gmail.com' );
		$I->amOnPage ( "/rest-users/getOne/500" );
		$I->see ( 'No result found for primary key(s): 500' );
		$I->amOnPage ( "/rest-users/connect/" );
		$I->see ( 'Bearer' );
		$I->amOnPage ( "/rest-users/get/firstname+like+%27B%25%27" );
		$I->see ( '"count":7' );
	}

	// tests
	public function tryGotoAdminConfig(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Config", $I );
		$I->click ( '#edit-config-btn' );
		$I->waitForElement ( "#save-config-btn", self::TIMEOUT );
		$this->waitAndclick ( $I, "#save-config-btn", "#main-content" );
		$I->waitForElementClickable ( "#edit-config-btn", self::TIMEOUT );
		// $I->see ( "http://dev.local/" );
	}

	// tests
	public function tryGotoAdminThemes(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Themes", $I );
		$I->canSee ( "Themes module", "body" );
		$I->click ( '._saveConfig' );
		$this->waitAndclick ( $I, "._setTheme[href='Admin/setTheme/foundation']" );
		$I->amOnPage ( "/" );
		$I->canSee ( "foundation" );
		$this->gotoAdminModule ( "Admin/Themes", $I );
		$this->waitAndclick ( $I, "._setTheme[href='Admin/setTheme/semantic']" );
		$I->amOnPage ( "/" );
		$I->canSee ( "semantic" );
	}

	// tests
	/*
	 * public function tryGotoAdminGit(AcceptanceTester $I) {
	 * $this->gotoAdminModule ( "Admin/Git", $I );
	 * }
	 */
	// tests
	public function tryGotoAdminSeo(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Seo", $I );
		$I->click ( "#generateRobots" );
		$I->waitForText ( "Can not generate robots.txt if no SEO controller is selected.", self::TIMEOUT, "body" );
		$this->waitAndclick ( $I, "#addNewSeo", "body" );
		$I->waitForText ( "Creating a new Seo controller", self::TIMEOUT, "body" );
		$I->fillField ( "#controllerName", "TestSEOController" );
		$this->waitAndclick ( $I, "#action-modalNewSeo-0" );
		$I->waitForText ( "The TestSEOController controller has been created" );
		$I->wait ( 5 );
		$this->gotoAdminModule ( "Admin/Seo", $I );
		$this->waitAndclick ( $I, "#seoCtrls-tr-controllersTestSEOController" );
		$I->waitForText ( "Change Frequency", self::TIMEOUT, "body" );
		$I->amOnPage ( "/TestSEOController" );
		$I->canSeeInSource ( "http://www.sitemaps.org/schemas/sitemap/0.9" );
	}

	// tests
	public function tryGotoAdminLogs(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Logs", $I );
		$I->click ( "[data-url='deActivateLog']", "#menu-logs" );
		$I->waitForElement ( "#bt-apply", self::TIMEOUT );
	}

	// tests
	public function tryGotoAdminTranslate(AcceptanceTester $I) {
		$this->gotoAdminModule ( "Admin/Translate", $I );
	}
}

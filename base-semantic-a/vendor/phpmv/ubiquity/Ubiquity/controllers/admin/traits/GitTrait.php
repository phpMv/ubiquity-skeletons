<?php

namespace Ubiquity\controllers\admin\traits;

use Cz\Git\GitRepository;
use Ubiquity\controllers\Startup;
use Ubiquity\controllers\admin\popo\RepositoryGit;
use Ubiquity\utils\http\URequest;
use Ubiquity\utils\base\UString;
use Ubiquity\utils\git\GitFileStatus;
use Ajax\semantic\html\collections\HtmlMessage;
use Ubiquity\utils\base\UFileSystem;
use Cz\Git\GitException;
use Ubiquity\cache\CacheManager;
use Ubiquity\utils\base\UArray;

/**
 *
 * @author jc
 * @property \Ajax\php\ubiquity\JsUtils $jquery
 * @property \Ubiquity\views\View $view
 */
trait GitTrait{

	abstract public function _getAdminData();

	abstract public function _getAdminViewer();

	abstract public function _getAdminFiles();

	abstract public function loadView($viewName, $pData = NULL, $asString = false);

	abstract public function git();

	abstract protected function showConfMessage($content, $type, $url, $responseElement, $data, $attributes = NULL);

	abstract protected function showSimpleMessage($content, $type, $icon = "info", $timeout = NULL, $staticName = null): HtmlMessage;

	public function gitRefresh() {
		echo $this->_git ();
		echo $this->jquery->compile ( $this->view );
	}

	public function _git() {
	}

	protected function _getRepo($getfiles = true) {
		$gitRepo = RepositoryGit::init ( $getfiles );
		return $gitRepo;
	}

	public function gitInit() {
		$this->_getRepo ();
		$appDir=Startup::getApplicationDir ();
		GitRepository::init ( Startup::getApplicationDir () );
		$gitignoreFile=$appDir. DS . ".gitignore";
		if(!file_exists($gitignoreFile)){
			UFileSystem::openReplaceWriteFromTemplateFile(Startup::getFrameworkDir() . "/admin/templates/gitignore.tpl", $gitignoreFile, []);
		}
		$this->git ();
	}

	public function frmSettings() {
		$gitRepo = $this->_getRepo ();
		$this->_getAdminViewer ()->gitFrmSettings ( $gitRepo );
		$this->jquery->execOn ( "click", "#validate-btn", '$("#frmGitSettings").form("submit");' );
		$this->jquery->execOn ( "click", "#cancel-btn", '$("#frm").html("");' );
		$this->jquery->renderView ( $this->_getAdminFiles ()->getViewGitSettings () );
	}

	public function updateGitParams() {
		$gitRepo = $this->_getRepo ( false );
		$activeRemoteUrl = $gitRepo->getRemoteUrl ();
		$newRemoteUrl = URequest::post ( "remoteUrl" );
		if (UString::isNull ( $activeRemoteUrl )) {
			$gitRepo->getRepository ()->addRemote ( "origin", $newRemoteUrl );
		} elseif ($activeRemoteUrl != $newRemoteUrl) {
			$gitRepo->getRepository ()->setRemoteUrl ( "origin", $newRemoteUrl );
		}
		CacheManager::$cache->store ( RepositoryGit::$GIT_SETTINGS, "return " . UArray::asPhpArray ( $_POST, "array" ) . ";", true );
		$this->git ();
	}

	public function commit() {
		$filesToCommit = URequest::post ( "files-to-commit", [ ] );
		if (sizeof ( $filesToCommit ) > 0) {
			$messages = [ ];
			$countFilesToAdd = 0;
			$countFilesUpdated = 0;
			$countFilesIgnored = 0;
			$gitRepo = $this->_getRepo ( true );
			$repo = $gitRepo->getRepository ();
			$filesToAdd = [ ];
			$allFiles = $gitRepo->getFiles ();
			foreach ( $allFiles as $filename => $uFile ) {
				if (in_array ( $filename, $filesToCommit )) {
					$filesToAdd [] = $filename;
					if ($uFile->getStatus () == GitFileStatus::$UNTRACKED) {
						$countFilesToAdd ++;
					} else {
						$countFilesUpdated ++;
					}
				} else {
					if ($uFile->getStatus () != GitFileStatus::$UNTRACKED) {
						$countFilesIgnored ++;
					}
				}
			}

			$repo->addFile ( $filesToAdd );
			if ($countFilesToAdd > 0) {
				$messages [] = $countFilesToAdd . " new file(s) added";
			}
			if ($countFilesIgnored > 0) {
				$messages [] = $countFilesIgnored . " ignored file(s).";
			}
			if ($countFilesUpdated > 0) {
				$messages [] = $countFilesUpdated . " updated file(s).";
			}

			$message = URequest::post ( "summary", "No summary" );
			if (UString::isNotNull ( URequest::post ( "description", "" ) ))
				$message = [ $message,URequest::post ( "description" ) ];
			$repo->commit ( $message );
			$msg = $this->showSimpleMessage ( "Commit successfully completed!", "positive", "check square", null, "init-message" );
			$msg->addList ( $messages );
			$this->_refreshParts ();
		} else {
			$msg = $this->showSimpleMessage ( "Nothing to commit!", "", "warning circle", null, "init-message" );
		}
		echo $msg;
		echo $this->jquery->compile ( $this->view );
	}

	protected function _refreshParts() {
		$this->jquery->exec ( '$(".to-clear").html("");$(".to-clear-value").val("");', true );
		$this->jquery->get ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/refreshFiles", "#dtGitFiles", [ "attr" => "","jqueryDone" => "replaceWith","hasLoader" => false ] );
		$this->jquery->get ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/refreshCommits", "#dtCommits", [ "attr" => "","jqueryDone" => "replaceWith","hasLoader" => false ] );
	}

	public function gitPush() {
		$gitRepo = $this->_getRepo ( false );
		try {
			if ($gitRepo->setRepoRemoteUrl ()) {
				$repo = $gitRepo->getRepository ();
				$repo->push ( "origin master", [ "--set-upstream" ] );
				$msg = $this->showSimpleMessage ( "Push successfully completed!", "positive", "upload", null, "init-message" );
				$this->_refreshParts ();
			} else {
				$msg = $this->showSimpleMessage ( "Check your github settings before pushing! (user name, password or remote url)", "negative", "upload", null, "init-message" );
			}
		} catch ( GitException $ge ) {
			echo $ge->getMessage();
			$msg = $this->showSimpleMessage ( "Invalid github settings! (Check your user name, password or remote url)", "negative", "upload", null, "init-message" );
		}
		echo $msg;
		echo $this->jquery->compile ( $this->view );
	}

	public function gitPull() {
		$gitRepo = $this->_getRepo ( false );
		$repo = $gitRepo->getRepository ();
		$repo->pull ();
		$msg = $this->showSimpleMessage ( "Pull successfully completed!", "positive", "download", null, "init-message" );
		$this->_refreshParts ();
		echo $msg;
		echo $this->jquery->compile ( $this->view );
	}

	public function gitIgnoreEdit() {
		$this->jquery->postFormOnClick ( "#validate-btn", $this->_getAdminFiles ()->getAdminBaseRoute () . "/gitIgnoreValidate", "gitignore-frm", "#frm" );
		$this->jquery->execOn ( "click", "#cancel-btn", '$("#frm").html("");' );
		$content = UFileSystem::load ( Startup::getApplicationDir () . DS . ".gitignore" );
		if ($content === false) {
			$content = "#gitignorefile\n";
		}
		$this->jquery->renderView ( $this->_getAdminFiles ()->getViewGitIgnore (), [ "content" => $content ] );
	}

	public function gitIgnoreValidate() {
		if (URequest::isPost ()) {
			$content = URequest::post ( "content" );
			if (UFileSystem::save ( Startup::getApplicationDir () . DS . ".gitignore", $content )) {
				$this->jquery->get ( $this->_getAdminFiles ()->getAdminBaseRoute () . "/refreshFiles", "#dtGitFiles", [ "attr" => "","jqueryDone" => "replaceWith","hasLoader" => false ] );
				$message = $this->showSimpleMessage ( "<b>.gitignore</b> file saved !", "positive", "git" );
			} else {
				$message = $this->showSimpleMessage ( "<b>.gitignore</b> file not saved !", "warning", "git" );
			}
		}
		echo $message;
		echo $this->jquery->compile ( $this->view );
	}

	public function refreshFiles() {
		$gitRepo = $this->_getRepo ();
		$files=$gitRepo->getFiles ();
		echo $this->_getAdminViewer ()->getGitFilesDataTable ( $files );
		$this->jquery->exec('$("#lbl-changed").toggle('.((sizeof($files)>0)?"true":"false").');',true);
		echo $this->jquery->compile ( $this->view );
	}
	
	public function refreshCommits() {
		$gitRepo = $this->_getRepo ( false );
		echo $this->_getAdminViewer ()->getGitCommitsDataTable ( $gitRepo->getCommits () );
		echo $this->jquery->compile ( $this->view );
	}

	public function changesInfiles(...$filenameParts) {
		$filename = implode ( DS, $filenameParts );
		$gitRepo = $this->_getRepo ( false );
		$changes = $gitRepo->getRepository ()->getChangesInFile ( $filename );
		if (UString::isNull ( $changes )) {
			$changes = str_replace ( PHP_EOL, " ", UFileSystem::load ( Startup::getApplicationDir () . DS . $filename ) );
		}
		$this->jquery->exec ( 'var value=\'' . htmlentities ( str_replace ( "'", "\\'", str_replace ( "\u", "\\\u", $changes ) ) ) . '\';$("#changes-in-file").html(Diff2Html.getPrettyHtml($("<div/>").html(value).text()),{inputFormat: "diff", showFiles: true, matching: "lines"});', true );
		echo '<div id="changes-in-file"></div>';
		echo $this->jquery->compile ( $this->view );
	}

	public function changesInCommit($commitHash) {
		$gitRepo = $this->_getRepo ( false );
		$changes = $gitRepo->getRepository ()->getChangesInCommit ( $commitHash );
		if (UString::isNull ( $changes )) {
			$changes = "No change";
		}
		$this->jquery->exec ( 'var value=\'' . htmlentities ( str_replace ( "'", "\\'", str_replace ( "\u", "\\\u", $changes ) ) ) . '\';var diff2htmlUi = new Diff2HtmlUI({diff: $("<div/>").html(value).text()});diff2htmlUi.draw("#changes-in-commit", {inputFormat: "diff", showFiles: true, matching: "lines"});diff2htmlUi.fileListCloseable("#changes-in-commit", true);', true );
		echo '<div id="changes-in-commit"></div>';
		echo $this->jquery->compile ( $this->view );
	}
}

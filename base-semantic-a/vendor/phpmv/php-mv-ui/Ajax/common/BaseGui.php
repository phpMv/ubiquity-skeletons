<?php

namespace Ajax\common;


use Ajax\common\components\SimpleComponent;
use Ajax\JsUtils;
use Ajax\common\html\BaseHtml;

/**
 * BaseGui Phalcon library
 * @author jcheron
 * @version 1.001
 */

/**
 * BaseGui
 */
class BaseGui {
	protected $autoCompile;
	protected $components;
	protected $htmlComponents;
	/**
	 *
	 * @var \Ajax\JsUtils
	 */
	protected $js;

	public function __construct($autoCompile=true) {
		$this->autoCompile=$autoCompile;
		$this->components=array ();
		$this->htmlComponents=array ();
	}

	public function isAutoCompile() {
		return $this->autoCompile;
	}

	public function setAutoCompile($autoCompile) {
		$this->autoCompile=$autoCompile;
		return $this;
	}

	public function compile($internal=false) {
		if ($internal===false&&$this->autoCompile===true)
			throw new \Exception("Impossible to compile if autoCompile is set to 'true'");
		foreach ( $this->components as $component ) {
			$component->compile();
		}
	}

	public function setJs(JsUtils $js) {
		$this->js=$js;
	}

	public function addComponent(SimpleComponent $component, $attachTo, $params) {
		if ($this->autoCompile)
			$this->components []=$component;
		if (isset($attachTo))
			$component->attach($attachTo);
		if (isset($params))
			if (\is_array($params))
				$component->setParams($params);
		return $component;
	}

	public function addHtmlComponent(BaseHtml $htmlComponent) {
		$this->htmlComponents [$htmlComponent->getIdentifier()]=$htmlComponent;
		return $htmlComponent;
	}

	public function compileHtml(JsUtils $js=NULL, &$view=NULL) {
		foreach ( $this->htmlComponents as $htmlComponent ) {
			$htmlComponent->compile($js, $view);
		}
	}
}

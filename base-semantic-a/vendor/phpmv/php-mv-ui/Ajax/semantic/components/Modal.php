<?php

namespace Ajax\semantic\components;

use Ajax\JsUtils;

class Modal extends SimpleSemExtComponent {

	public function __construct(JsUtils $js) {
		parent::__construct($js);
		$this->uiName="modal";
	}

	protected function setBehavior($name) {
		$this->paramParts[]=[$name];
		return $this;
	}

	public function showDimmer(){
		return $this->setBehavior("hide dimmer");
	}

	public function setInverted(){
		$this->params["inverted"]=true;
	}

	public function setOnHidden($jsCode) {
		$jsCode=str_ireplace("\"","%quote%", $jsCode);
		return $this->setParam("onHidden", "%function(){".$jsCode."}%");
	}
}

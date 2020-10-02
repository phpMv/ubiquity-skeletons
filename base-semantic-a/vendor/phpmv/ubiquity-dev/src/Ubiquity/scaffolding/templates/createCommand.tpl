<?php
%namespace%

use Ubiquity\devtools\cmd\commands\AbstractCustomCommand;
use Ubiquity\devtools\cmd\ConsoleFormatter;
%uses%

class %classname% extends AbstractCustomCommand {

	protected function getValue(): string {
		return '%value%';
	}

	protected function getAliases(): array {
		return %aliases%;
	}

	protected function getName(): string {
		return '%name%';
	}

	protected function getParameters(): array {
		return %parameters%;
	}

	protected function getExamples(): array {
		return ['Sample use of %name%'=>'Ubiquity %name% %value%'];
	}

	protected function getDescription(): string {
		return '%description%';
	}

	public function run($config, $options, $what, ...$otherArgs) {
		//TODO implement command behavior
		echo ConsoleFormatter::showInfo('Run %name% command');
	}
}


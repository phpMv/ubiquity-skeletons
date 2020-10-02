<?php
namespace Ubiquity\devtools\cmd;

/**
 * Ubiquity\devtools\cmd$Console
 * This class is part of Ubiquity
 *
 * @author jc
 * @version 1.0.0
 *
 */
class Console {

	/**
	 * Read a line from the user input.
	 *
	 * @return string
	 */
	public static function readline() {
		return \rtrim(\fgets(STDIN));
	}

	/**
	 * Ask the user a question and return the answer.
	 *
	 * @param string $prompt
	 * @param ?array $propositions
	 * @return string
	 */
	public static function question($prompt, array $propositions = null) {
		echo ConsoleFormatter::colorize($prompt, ConsoleFormatter::BLACK, ConsoleFormatter::BG_YELLOW);
		if (is_array($propositions)) {
			if (sizeof($propositions) > 2) {
				$props = "";
				foreach ($propositions as $index => $prop) {
					$props .= "[" . ($index + 1) . "] " . $prop . "\n";
				}
				echo ConsoleFormatter::formatContent($props);
				do {
					$answer = self::readline();
				} while ((int) $answer != $answer || ! isset($propositions[(int) $answer - 1]));
				$answer = $propositions[(int) $answer - 1];
			} else {
				echo " (" . implode("/", $propositions) . ")\n";
				do {
					$answer = self::readline();
				} while (array_search($answer, $propositions) === false);
			}
		} else {
			$answer = self::readline();
		}

		return $answer;
	}

	/**
	 * Returns true if the answer is yes or y.
	 *
	 * @param string $answer
	 * @return boolean
	 */
	public static function isYes($answer) {
		return \array_search($answer, [
			"yes",
			"y"
		]) !== false;
	}

	/**
	 * Returns true if the answer is no or n.
	 *
	 * @param string $answer
	 * @return boolean
	 */
	public static function isNo($answer) {
		return \array_search($answer, [
			"no",
			"n"
		]) !== false;
	}

	/**
	 * Returns true if the answer is cancel or z.
	 *
	 * @param string $answer
	 * @return boolean
	 */
	public static function isCancel($answer) {
		return \array_search($answer, [
			"cancel",
			"z"
		]) !== false;
	}
}

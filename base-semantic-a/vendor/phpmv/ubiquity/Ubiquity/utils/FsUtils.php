<?php

namespace Ubiquity\utils;

/**
 * File system utilities
 * @author jc
 * @version 1.0.0.1
 */
class FsUtils {

	public static function glob_recursive($pattern, $flags=0) {
		$files=\glob($pattern, $flags);
		foreach ( \glob(\dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir ) {
			$files=\array_merge($files, self::glob_recursive($dir . '/' . \basename($pattern), $flags));
		}
		return $files;
	}

	public static function deleteAllFilesFromFolder($folder) {
		$files=\glob($folder . '/*');
		foreach ( $files as $file ) {
			if (\is_file($file))
				\unlink($file);
		}
	}

	public static function safeMkdir($dir) {
		if (!\is_dir($dir))
			return \mkdir($dir, 0777, true);
		return true;
	}

	public static function cleanPathname($path) {
		if (StrUtils::isNotNull($path)) {
			if (DS === "/")
				$path=\str_replace("\\", DS, $path);
			else
				$path=\str_replace("/", DS, $path);
			$path=\str_replace(DS . DS, DS, $path);
			if (!StrUtils::endswith($path, DS)) {
				$path=$path . DS;
			}
		}
		return $path;
	}

	public static function openReplaceInTemplateFile($source, $keyAndValues) {
		if (\file_exists($source)) {
			$str=\file_get_contents($source);
			return self::replaceFromTemplate($str, $keyAndValues);
		}
		return false;
	}

	public static function openReplaceWriteFromTemplateFile($source, $destination, $keyAndValues) {
		if (($str=self::openReplaceInTemplateFile($source, $keyAndValues))) {
			return \file_put_contents($destination, $str, LOCK_EX);
		}
		return false;
	}

	public static function replaceFromTemplate($content, $keyAndValues) {
		array_walk($keyAndValues, function (&$item) {
			if (\is_array($item))
				$item=\implode("\n", $item);
		});
		$str=\str_replace(array_keys($keyAndValues), array_values($keyAndValues), $content);
		return $str;
	}

	public static function replaceWriteFromContent($content, $destination, $keyAndValues) {
		return \file_put_contents($destination, self::replaceFromTemplate($content, $keyAndValues), LOCK_EX);
	}

	public static function tryToRequire($file) {
		if (\file_exists($file)) {
			require_once ($file);
			return true;
		}
		return false;
	}
}

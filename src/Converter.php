<?php

namespace Weird\Translator;

class Converter {

	public static function convertArrayToObject($array){
		foreach ($array as $key => $value) {
			if (is_array($value)){
				$array[$key] = static::convertArrayToObject($value);
			}
		}
		return (object) $array;
	}

	public static function convertObjectToArray($object){
		$new = [];
		foreach ((array)$object as $key => $value) {
			$new[$key] = $value;
		}
		return (array) $new;
	}
}
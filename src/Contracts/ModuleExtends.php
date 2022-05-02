<?php
namespace Weird\Translator\Contracts;

use stdClass;
use Weird\Translator\Exception\ModuleSettingsNotFoundException;

class ModuleExtends {
	private array $moduleSettings = [];

	public function log($class, $text){
		$modName = @end(explode('\\', $class));
		dump('['.$modName.']: '.$text);
	}

	private static function convertObjectToArray($object){
		$new = [];
		foreach ((array)$object as $key => $value) {
			if (is_object($value)) $new[$key] = static::convertObjectToArray($value);
			else $new[$key] = $value;
		}
		return (array) $new;
	}

	public function setSettings($settings){
		if (is_array($settings)){
			$this->moduleSettings = $settings;
		} else if ($settings instanceof stdClass) {
			$this->moduleSettings = static::convertObjectToArray($settings);
		}
	}

	public function getSettings(){
		if (!empty($this->moduleSettings)){
			return $this->moduleSettings;
		}
		throw new ModuleSettingsNotFoundException();
	}

}
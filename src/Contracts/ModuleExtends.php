<?php
namespace Weird\Translator\Contracts;

use stdClass;
use Weird\Translator\Exception\ModuleSettingsNotFoundException;

class ModuleExtends {
	private array $moduleSettings = [];

	public function setSettings($settings){
		if (is_array($settings)){
			$this->moduleSettings = $settings;
		} else if ($settings instanceof stdClass) {
			$this->moduleSettings = (array) $settings;
		}
	}

	public function getSettings(){
		if (!empty($this->moduleSettings)){
			return $this->moduleSettings;
		}
		throw new ModuleSettingsNotFoundException();
	}

}
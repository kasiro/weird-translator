<?php


namespace Weird\Translator;

use stdClass;
use RuntimeException;
use Weird\Translator\Exception\ModuleAliasNotFoundException;


final class Config {
	private $configExtension = '.config';

	/**
	 * @var string $filePath - путь до обрабатываемого файла
	 */
	private string $filePath;
	private array $modules = [];

	public function initModules(string $filePath): void {
		$this->filePath = $filePath;
		$file = file_get_contents($filePath);
		$strr = '#\/\*\*\n \* @jhpdoc\n \*\/#m';
		$hasDocParse = (bool) preg_match('#\/\*\*\n \*\? @status parse(.*?)\*\/#ms', $file, $matches);
		$hasDockBlock = (bool) preg_match($strr, $file);
		dump('[hasDocBlok]: '.($hasDockBlock ? 'true' : 'false'));
		dump('[hasDocParse]: '.($hasDocParse ? 'true' : 'false'));
		// Тут идет проверка есть ли в файле блок-комментарий
		if (!$hasDockBlock){
			if (!$hasDocParse) {
				$jsonPath = $this->findConfigFile($filePath);
				if (!$jsonPath) {
					$this->defaultModules();
					return;
				}
				$this->configureJsonFile($jsonPath);
			} else {
				//* Парсим DocBlock
				//* Настраиваем модули
				$dockBlock = $matches[1];
				// dd($dockBlock);
				$this->configureDockBlock($dockBlock);
			}
		} else {
			$this->defaultModules();
			$this->replaceDoc();
		}
	}

	public function getAliasName($moduleName){
		return strtolower(strtr($moduleName, ['Module' => '']));
	}

	public function configureDockBlock($dockBlock){
		$modules = [];
		preg_match_all('#@module (.*) {(.*)}#m', $dockBlock, $matches);
		for ($i = 0; $i < count($matches[1]); $i++) { 
			$moduleName = $matches[1][$i];
			$moduleSettings = $matches[2][$i];
			$m = $this->getAliasName($moduleName);
			if ($moduleSettings == ''){
				//! Модули не определены
				// $moduleSettings = $this->getDefaultModuleSettings($m);
			}
			$modules[$m] = $moduleSettings;
		}
		dd($modules);
	}

	public function replaceDoc() {
		$strr = '#\/\*\*\n \* @jhpdoc\n \*\/#m';
		$modules = array_map(
			fn($m) => @end(explode('\\', $m)),
			$this->getModules()
		);
		$str = '/**' . PHP_EOL;
		$str .= ' *? @status parse'.PHP_EOL;
		foreach ($modules as $module){
			$m = $this->getAliasName($module);
			$module_settings = $this->getDefaultModuleSettings($m);
			$settings = [];
			if (count($module_settings) > 1){
				$i = 0;
				foreach ($module_settings as $el){
					$key = array_keys($module_settings)[$i];
					$value = array_values($module_settings)[$i];
					if (is_bool($value)) $value = $value ? 'true' : 'false';
					$settings[] = $key.' => '.$value;
					$i++;
				}
			} elseif (!empty($module_settings)) {
				$key = array_key_first($module_settings);
				$value = array_values($module_settings)[0];
				if (is_bool($value)) $value = $value ? 'true' : 'false';
				$settings[] = $key.' => '.$value;
			}
			$str .= ' ** @module '.$module.' {'.implode(', ', $settings).'}'.PHP_EOL;
		}
		$str .= ' */';
		$text = file_get_contents($this->filePath);
		$text = preg_replace($strr, $str, $text);
		file_put_contents($this->filePath, $text);
	}

	/**
	 * Поиск конфиг-файла
	 */
	public function findConfigFile($file_path) {
		$arr = explode('/', dirname($file_path));
		$DirPath = dirname($file_path);
		for ($i = 0; $i < count($arr); $i++){
			$cur_dir_up = $DirPath;
			$files = glob($cur_dir_up . '/*.config');
			if (!empty($files)){
				foreach ($files as $file){
					if (basename($file) === 'jhp.config'){
						return $file;
					}
				}
			}
			$DirPath = dirname($DirPath);
		}
		return false;
	}

	private function configureJsonFile(string $filepath): void {
		$content = file_get_contents($filepath);
		if (strlen($content) == 0) $this->createDefaultConfig($filepath);
		$content = file_get_contents($filepath);
		$file = json_decode($content, true);
		// dd($file);
		$fileModules = $this->convertAliasToName($file['modules']);
		// dd($fileModules);
		// Лишь бы работало (Затычка)
		// $fileModules = [];
		// foreach (glob(__DIR__.'/Modules/*.php') as $module){
		// 	$name = explode('.', basename($module))[0];
		// 	$fileModules[] = 'Weird\Translator\Modules\\'.$name;
		// }
		$customModules = isset($file->customModules) ? $file->customModules : [];
		$this->modules = array_merge($fileModules, $customModules);
	}

	private function convertAliasToName($aliases) {
		// FIXME: Переделать Механизм Кофига
		$aliasesFile = require('aliases.php');
		$modules = [];
		foreach ($aliases as $aliasKey => $alias) {
			if (!array_key_exists($aliasKey, $aliasesFile)) {
				throw new ModuleAliasNotFoundException($alias);
			}
			$modules[] = $aliasesFile[$aliasKey];
		}
		return $modules;
	}

	public function getModuleSettings($moduleName) {
		$confPath = $this->findConfigFile($this->filePath);
		$content = file_get_contents($confPath);
		$file = json_decode($content);
		$file = static::convertObjectToArray($file);
		foreach ($file['modules'] as $modName => $settings){
			if ($moduleName == $modName){
				return $settings;
			}
		}
		return [];
	}

	public static function convertObjectToArray($object) {
		$new = [];
		foreach ((array)$object as $key => $value) {
			if (is_object($value)) $new[$key] = static::convertObjectToArray($value);
			else $new[$key] = $value;
		}
		return (array) $new;
	}

	public function getDefaultModuleSettings($moduleName = '', $getAll = false) {
		$modules = $this->getModules();
		$std = new stdClass;
		$userSettings = [
			'use' => [
				"muxuse" => false
			]
		];
		$files = glob(__DIR__.'/Modules/*.php');
		$defaultSettings = [];
		foreach ($files as $modPath) {
			$modPath = basename(explode('.', $modPath)[0]);
			$defaultSettings[$this->getAliasName($modPath)] = $std;
		}
		$defaultSettings = [...$defaultSettings, ...$userSettings];
		if ($getAll) return $defaultSettings;
		// $defaultSettings = static::convertObjectToArray($defaultSettings);
		if (isset($defaultSettings[$moduleName])){
			return static::convertObjectToArray($defaultSettings[$moduleName]);
		}
		return [];
	}

	public function createDefaultConfig($filepath){
		$text = $this->getDefaultModuleSettings(getAll: true);
		$json_sett = json_encode(['modules' => $text], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$json_sett = str_replace('    ', "\t", $json_sett);
		file_put_contents($filepath, $json_sett);
	}

	private function defaultModules(): void {
		$files = glob(__DIR__.'/Modules/*.php');
		foreach ($files as $modPath) {
			$name = explode('.', basename($modPath))[0];
			$this->modules[] = 'Weird\Translator\Modules\\'.$name;
		}
	}

	public function getModules(): array {
		if (!$this->modules) {
			throw new RuntimeException('Modules not found');
		}

		return $this->modules;
	}
}
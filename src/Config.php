<?php


namespace Weird\Translator;

use stdClass;
use RuntimeException;
use Weird\Translator\Exception\ModuleAliasNotFoundException;


final class Config {
	private $configExtension = '.config';

	private string $filePath;
	private array $modules = [];

	public function initModules(string $filePath): void 
	{
		$this->filePath = $filePath;
		$text = '';
		// $text = file_get_contents($filePath);
		if (str_contains($text, '\/\*\*\n \* @jhpdoc\n \*\/')){
			$modulesDoc = $this->getModulesDoc();
		} else {
			$jsonPath = $this->findConfigFile($filePath);
			// Если не нашли конфиг-файл в этой директории, то мы устанавливаем поумолчанию
			if (!$jsonPath) {
				$this->defaultModules();
				return;
			}
			$this->configureJsonFile($jsonPath);
		}
	}

	public function getModulesDoc() {
		$modules = $this->getModules();
		$strr = '\/\*\*\n \* @jhpdoc\n \*\/';
		dd($module);
		$str = '/**' . PHP_EOL;
		$str .= ' * @';
		return '';
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

	public static function convertObjectToArray($object)
	{
		$new = [];
		foreach ((array)$object as $key => $value) {
			if (is_object($value)) $new[$key] = static::convertObjectToArray($value);
			else $new[$key] = $value;
		}
		return (array) $new;
	}

	public function getDefaultModuleSettings($moduleName = '', $getAll = false) 
	{
		$modules = $this->getModules();
		dd($modules);
		$defaultSettings = [
			'fn' => new stdClass,
			'import' => new stdClass,
			'jsclass' => new stdClass,
			'quantifers' => new stdClass,
			'use' => [
				"muxuse" => false
			]
		];
		if ($getAll) return $defaultSettings;
		dd(class_exists('Converter'));
		$defaultSettings = Converter::convertObjectToArray($defaultSettings);
		return isset($defaultSettings[$moduleName]) ? $defaultSettings[$moduleName] : [];
	}

	public function createDefaultConfig($filepath){
		$text = $this->getDefaultModuleSettings(getAll: true);
		$json_sett = json_encode(['modules' => $text], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$json_sett = str_replace('    ', "\t", $json_sett);
		file_put_contents($filepath, $json_sett);
	}

	private function defaultModules(): void 
	{
		$this->modules = [
			FnModule::class
		];
	}

	public function getModules(): array {
		if (!$this->modules) {
			throw new RuntimeException('Modules not found');
		}

		return $this->modules;
	}
}
<?php


namespace Weird\Translator;

use stdClass;
use RuntimeException;
use Weird\Translator\Exception\ModuleAliasNotFoundException;
use converter;

final class Config {
	
	

	private $configExtension = '.config.json';

	private string $filePath;
	private array $modules = [];

	public function initModules(string $filePath): void {
		$this->filePath = $filePath;
		$jsonPath = $this->findConfigFile();

		// Если не нашли конфиг-файл в этой директории, то мы устанавливаем поумолчанию
		if (!$jsonPath) {
			$this->defaultModules();
			return;
		}

		$this->configureJsonFile($jsonPath);
	}

	/**
	 * Поиск конфиг-файла
	 */
	public function findConfigFile() {
		$pathInfo = pathinfo($this->filePath);

		$directory = $pathInfo['dirname'];
		$configFile = $directory.'/'.$pathInfo['filename'].$this->configExtension;

		if (file_exists($configFile)) return $configFile;
		return false;
	}

	private function configureJsonFile(string $filepath): void {
		$content = file_get_contents($filepath);
		if (strlen($content) == 0) $this->createDefaultConfig($filepath);
		$content = file_get_contents($filepath);
		$file = json_decode($content, true);
		var_dump($file);
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
		$confPath = $this->findConfigFile();
		$content = file_get_contents($confPath);
		$file = json_decode($content);
		foreach ($file['modules'] as $modName => $settings){
			if ($moduleName == $modName){
				return $settings;
			}
		}
		return [];
	}

	public function getDefaultModuleSettings($moduleName = '', $getAll = false) {
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
		var_dump(class_exists('converter'));
		// $defaultSettings = converter::convertObjectToArray($defaultSettings);
		return isset($defaultSettings[$moduleName]) ? $defaultSettings[$moduleName] : [];
	}

	public function createDefaultConfig($filepath){
		$text = $this->getDefaultModuleSettings(getAll: true);
		$json_sett = json_encode(['modules' => $text], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		$json_sett = str_replace('    ', "\t", $json_sett);
		file_put_contents($filepath, $json_sett);
	}

	private function defaultModules(): void {
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
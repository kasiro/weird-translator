<?php


namespace Weird\Translator;


use Weird\Translator\Exception\ModuleAliasNotFoundException;
use RuntimeException;
use Weird\Translator\Modules\FnModule;

final class Config
{
	private $configExtension = '.config.json';

	private string $filePath;
	private array $modules = [];

	public function initModules(string $filePath): void
	{
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
	private function findConfigFile()
	{
		$pathInfo = pathinfo($this->filePath);

		$directory = $pathInfo['dirname'];
		$configFile = $directory.'/'.$pathInfo['filename'].$this->configExtension;

		if (file_exists($configFile)) {
			return $configFile;
		}

		return false;
	}

	private function configureJsonFile(string $filepath): void
	{
		$content = file_get_contents($filepath);

		$file = json_decode($content);

		$fileModules = $this->convertAliasToName($file->modules);
		$customModules = isset($file->customModules)
			? $file->customModules
			: [];

		$this->modules = array_merge($fileModules, $customModules);
	}


	private function convertAliasToName(array $aliases)
	{
		$aliasesFile = require('aliases.php');

		$modules = [];

		foreach ($aliases as $alias) {
			if (!array_key_exists($alias, $aliasesFile)) {
				throw new ModuleAliasNotFoundException($alias);
			}

			$modules[] = $aliasesFile[$alias];
		}

		return $modules;
	}

	private function defaultModules(): void
	{
		$this->modules = [
			FnModule::class
		];
	}

	public function getModules(): array
	{
		if (!$this->modules) {
			throw new RuntimeException('Modules not found');
		}

		return $this->modules;
	}
}
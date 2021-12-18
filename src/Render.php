<?php


namespace Weird\Translator;


use Weird\Translator\Contracts\ModuleInterface;

final class Render
{
	private array  $modules;
	private string $filePath;

	public function render(string $filePath, array $modules)
	{
		$this->setFilePath($filePath);
		$this->setModules($modules);

		$content = file_get_contents($filePath);

		/**
		 * @var ModuleInterface $module
		*/
		foreach ($modules as $key => $module) {
			// FIXME: тут у нас будет проблема, если конструктору нужны параметры
			$module = new $module();

			if (!($module instanceof ModuleInterface)) {
				throw new \RuntimeException('The module "'.get_class($module).'" don"t extended from ModuleInterface');
			}

			$content = $module->process($content);
		}

		return $content;
	}

	public function setFilePath(string $path)
	{
		$this->filePath = $path;
	}
	public function setModules(array $modules)
	{
		$this->modules = $modules;

	}

}
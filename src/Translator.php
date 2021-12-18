<?php

namespace Weird\Translator;

final class Translator
{
	private Config $config;
	private Render $render;

	private string $filePath;
	private bool $overwrite = true;

	public function __construct()
	{
		$this->config = new Config();
		$this->render = new Render();
	}

	public function render(string $filePath): string
	{
		$this->setPath($filePath);
		$this->config->initModules($this->filePath);

		$modules = $this->config->getModules();
		$content = $this->render->render($this->filePath, $modules);

		return $this->saveFile($content);
	}

	private function saveFile(string $content): string
	{
		$pathInfo = pathinfo($this->filePath);

		$outFilePath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.php';

		if (!$this->overwrite && file_exists($outFilePath)) {
			throw new \DomainException('File "' . $outFilePath . '" exists and overwrite off');
		}


		file_put_contents($outFilePath, $content);

		return $outFilePath;
	}


	public function setPath(string $filePath): void
	{
		$this->filePath = ROOT . '/' . $filePath;
	}
}
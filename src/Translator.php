<?php

namespace Weird\Translator;

final class Translator
{
	private Config $config;
	private Render $render;

	private string $filePath;

	public function __construct() {
		$this->config = new Config();
		$this->render = new Render();
		$this->render->config = &$this->config;
	}

	/**
	 * @param string $filePath - Путь до обрабатываемого jhp-файла
	 * */
	public function render(string $filePath): string {
		$this->setPath($filePath);
		$this->config->initModules($this->filePath);

		$modules = $this->config->getModules();
		$content = $this->render->render($this->filePath, $modules);

		return $this->saveFile($content);
	}

	private function saveFile(string $content): string {
		$pathInfo = pathinfo($this->filePath);
		$outFilePath = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '.php';
		file_put_contents($outFilePath, $content);
		return $outFilePath;
	}


	public function setPath(string $filePath): void {
		$this->filePath = '';
		if (!str_contains($filePath, ROOT)){
			$temp = ROOT . '/' . $filePath;
		} else {
			$temp = $filePath;
		}
		if (str_contains($temp, '//')){
			$this->filePath = $filePath;
		} else {
			$this->filePath = $temp;
		}
	}
}
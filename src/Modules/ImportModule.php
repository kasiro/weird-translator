<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleExtends;
use Weird\Translator\Contracts\ModuleInterface;

final class ImportModule extends ModuleExtends implements ModuleInterface {

	public function __construct() {
		$this->path = ROOT . '/src/UserModules';
	}

	public function myrglob($base, $pattern, $flags = 0) {
		if (substr($base, -1) !== DIRECTORY_SEPARATOR) {
			$base .= DIRECTORY_SEPARATOR;
		}
	
		$files = glob($base.$pattern, $flags);
		
		foreach (glob($base.'*', GLOB_ONLYDIR|GLOB_NOSORT|GLOB_MARK) as $dir) {
			$dirFiles = $this->myrglob($dir, $pattern, $flags);
			if ($dirFiles !== false) {
				$files = array_merge($files, $dirFiles);
			}
		}
	
		return $files;
	}

	public function process(string $content): string {
		$this->content = $content;
		$content = preg_replace_callback('/(.*)import \'(.*)\';/m', function ($matches) {
			$tabs = $matches[1];
			$path = $matches[2];
			if (str_contains($tabs, '// ') || preg_match('/\w+/m', $tabs)){
				return $matches[0];
			}

			$files = '';
			$fullPath = $this->path.'/'.$path;

			if (file_exists("$fullPath.php")) {
				return "{$tabs}require '{$fullPath}';";
			}
			if (is_dir($fullPath) && file_exists($fullPath)) {
				$filesOfDirectory = $this->myrglob("$fullPath", '*.php');
				if (!empty($filesOfDirectory)){
					foreach ($filesOfDirectory as $file){
						$files .= "{$tabs}require '{$file}';\n";
					}
				}
			}

			$files = strtr(strtr($files, "\n", ''), [
				';require' => ";\nrequire"
			]);
			
			return $files;
		}, $content);

		$content = preg_replace_callback('/(.*)import: include \'(.*)\';/m', function ($matches) {
			$tabs = $matches[1];
			$paths = $matches[2];
			$mp = $this->path;
			
			/**
			 * Необходимые данные:
			 * 	Полный путь до файла jhp
			 * 	Получить настройки модуля из Конфига
			 */
			if (str_contains($tabs, '// ') || preg_match('/\w+/m', $tabs)){
				return $matches[0];
			}
			
		}, $content);
		return $content;
	}

}
<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Config;
use Weird\Translator\Contracts\ModuleExtends;
use Weird\Translator\Contracts\ModuleInterface;

use function PHPUnit\Framework\returnSelf;

final class UseModule extends ModuleExtends implements ModuleInterface {

	public function process(string $content): string {
		$loader = $GLOBALS['loader'];
		$this->log(__CLASS__, 'loader -> '.(is_object($loader) ? 'true' : 'false'));
		foreach ($loader->getPrefixesPsr4() as $key => $array){
			$prefix = $key;
			$v = $array[0];
			break;
		}
		
		$settings = $this->getSettings()['muxuse'];
		$settings = true;
		$this->log(__CLASS__, 'settings -> '.(is_array($settings) ? 'true' : 'false'));
		$content = match ($settings){
			true => preg_replace_callback('/^use (.*);$/m', function ($matches) use ($prefix, $v) {
				$namespace = $matches[1];
				$s = '';
				if (str_contains($namespace, $prefix)){
					$fname = @end(explode('/', $v));
					$path = str_replace($prefix, $fname.'/', $namespace);
					$path = str_replace('\\', '/', $path);
					$fullpath = ROOT.'/'.$path.'.php';
					$files = glob($fullpath);
					$s .= '{'.PHP_EOL;
					foreach ($files as $file){
						$s .= "\t";
						$c = file_get_contents($file);
						$c = preg_match('/class (\w+)/m', $c, $matchesClass);
						$className = $matchesClass[1];
						
						if ($file != @end($files)){
							$s .= $className.','.PHP_EOL;
						} else {
							$s .= $className.PHP_EOL;
						}
					}
					$s .= '}';
					$newNamespace = str_replace('*', $s, $matches[0]);
				} else return $matches[0];
				// dd($matches[0], $newNamespace, $s);
				return $newNamespace;
			}, $content),
			false => preg_replace_callback('/^use (.*);$/m', function ($matches) use ($prefix, $v) {
				$namespace = $matches[1];
				$s = '';
				if (str_contains($namespace, $prefix)){
					$fname = @end(explode('/', $v));
					$path = str_replace($prefix, $fname.'/', $namespace);
					$path = str_replace('\\', '/', $path);
					$fullpath = ROOT.'/'.$path.'.php';
					$files = glob($fullpath);
					foreach ($files as $file){
						$c = file_get_contents($file);
						$c = preg_match('/class (\w+)/m', $c, $matches);
						$className = $matches[1];
						$newNamespace = str_replace('*', '', $namespace);
						$s .= 'use '.$newNamespace.$className.';'.PHP_EOL;
					}
				} else return $matches[0];
				return $s;
			}, $content)
		};
		// dd($content);
		// TODO: Получить из конфига настройки модуля
		return $content;
	}

}
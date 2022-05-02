<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleInterface;
use Weird\Translator\Contracts\ModuleExtends;

final class JsClassModule extends ModuleExtends implements ModuleInterface {

	public function getSettings() {
		return ['ots' => false];
	}

	public function findType($sym){
		$ops = require __DIR__.'/types/types.php';
		$find = false;
		foreach ($ops as $el){
			if (in_array($el, $sym)) {
				$find = $el;
				break;
			}
		}
		foreach ($ops as $el){
			$sym = array_filter($sym, fn($e) => $e != $el);
		}
		$str = implode(' ', $sym);
		if (!empty($str)) $mat2 = " {$str} ";
		else $mat2 = ' ';
		return [$find, $mat2];
	}

	public function process(string $content): string {
		return preg_replace_callback(
			'/^([^\n\/\/].*public|[^\n\/\/].*private|[^\n\/\/].*protected|)[[:>:]](.*)[[:<:]](.*)(\((.*)\)|\()/m',
			function ($matches) {
				$sym = explode(' ', trim($matches[2]));
				if (!str_contains($matches[1], '\'')){
					if (!str_contains($matches[1], '"')){
						if (!in_array('function', $sym)){
							if (!str_contains($matches[0], '// ')){
								list($find, $mat2) = $this->findType($sym);
								if ($find) {
									if (array_key_exists('ots', $this->getSettings())){
										if ($this->getSettings()['ots'] === true) {
											return $matches[1] . $mat2 . 'function ' . $matches[3] . $matches[4] . ': '.$find.' ';
										} else {
											return $matches[1] . $mat2 . 'function ' . $matches[3] . $matches[4] . ': '.$find;
										}
									}
									return $matches[1] . $mat2 . 'function ' . $matches[3] . $matches[4] . ':'.$find;
								} else {
									return $matches[1] . $mat2 . 'function ' . $matches[3] . $matches[4];
								}
							}
						}
					}
				}
				return $matches[0];
			},
		$content);
	}

}
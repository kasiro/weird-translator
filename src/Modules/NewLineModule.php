<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleExtends;
use Weird\Translator\Contracts\ModuleInterface;

final class NewLineModule extends ModuleExtends implements ModuleInterface {

	public function process(string $content): string {
		$content = preg_replace('/nl (.*);/m', 'echo $1 . PHP_EOL;', $content);
		return $content;
	}

}
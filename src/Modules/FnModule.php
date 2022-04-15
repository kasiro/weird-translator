<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleExtends;
use Weird\Translator\Contracts\ModuleInterface;

final class FnModule extends ModuleExtends implements ModuleInterface {

	public function process(string $content): string {
		$content = preg_replace('/^([^\/\/].*|)fn((?<!\')(?<!\"))\((.*|)\) use \((\$.*)\) => {/m', '$1function ($3) use ($4) {', $content);
		$content = preg_replace('/^([^\/\/].+)(\$.*)[[:>:]] => {/m', '$1function ($2) {', $content);
		$content = preg_replace('/^([^\/\/].*|)fn\((.*|)\) => {/m', '$1function ($2) {', $content);
		return $content;
	}

}
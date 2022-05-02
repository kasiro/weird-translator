<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleExtends;
use Weird\Translator\Contracts\ModuleInterface;

final class CommentsModule extends ModuleExtends implements ModuleInterface {

	public function process(string $content): string {
		$content = preg_replace('/^[\t\s]*\/\/-\s*.+$/m', '', $content);
		return $content;
	}

}
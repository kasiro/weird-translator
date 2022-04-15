<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleExtends;
use Weird\Translator\Contracts\ModuleInterface;

final class QuantifersModule extends ModuleExtends implements ModuleInterface {

	public function process(string $content): string {
		$content = preg_replace_callback(
			'/(\t|\s{4})(public|private|protected):\n(\t\t|\s{8})(.*?;\n\n)/ms',
			function ($matches) {
				$tabs = $matches[1];
				$mode = $matches[2];
				$elss = $matches[4];
				$s = '';
				if (str_contains($tabs, '// ') || preg_match('/\w+/m', $tabs)){
					return $matches[0];
				}
				$els = explode(PHP_EOL, $elss);
				unset($els[count($els) - 1]);
				unset($els[count($els) - 1]);
				// dd($els);
				foreach ($els as $el){
					$el = str_replace("\t", '', $el);
					$s .= $tabs.$mode.' '.$el.PHP_EOL;
				}
				// dd($s);
				return $s.PHP_EOL;
			}, $content);

			if (preg_match('/(public|private|protected)(.*);\n\n/ms', $content)){
				$content = preg_replace_callback(
					'/(public|private|protected)(.*);\n\n/ms',
					function ($matches) {
						if (str_contains($matches[2], PHP_EOL.PHP_EOL)){
							return str_replace(PHP_EOL.PHP_EOL, PHP_EOL, $matches[0]).PHP_EOL;
						}
					},
				$content);
			}
		return $content;
	}

}
<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleInterface;
use Weird\Translator\Contracts\ModuleExtends;

final class EnumModule extends ModuleExtends implements ModuleInterface {

	public function process(string $content): string {
		$types = require __DIR__.'/types/types.php';
		if (preg_match('/^(\t*|\s*)enum (?<name>\w+): (?<type>\w+) -> (?<value>\[.*\]);?$/m', $content) != false){
			$content = preg_replace_callback(
				'/^(\t*|\s*)enum (?<name>\w+): (?<type>\w+) -> (?<value>\[.*\]);?$/m',
				function ($matches) use ($types) {
					$tabs = $matches[1];
					$name = $matches['name'];
					$type = $matches['type'];
					$value = $matches['value'];
					eval('$values = '.$value.';');
					$s = '';
					foreach ($values as $value){
						if (in_array($type, $types) && call_user_func('is_'.$type, $value)){
							if ($value != @end($values)){
								$s .= $tabs."\tcase ".$value.";".PHP_EOL;
							} else {
								$s .= $tabs."\tcase ".$value.";";
							}
						}
					}
					return $tabs.'enum '.$name.': '.$type." {\n".$s."\n".$tabs."}";
				},
			$content);
		}
		if (preg_match('/^(\t*|\s*)enum (?<name>\w+) -> (?<value>\[.*\]);?$/m', $content) != false){
			$content = preg_replace_callback(
				'/^(\t*|\s*)enum (?<name>\w+) -> (?<value>\[.*\]);?$/m',
				function ($matches) {
					$tabs = $matches[1];
					$name = $matches['name'];
					$value = $matches['value'];
					eval('$values = '.$value.';');
					$s = '';
					foreach ($values as $value){
						if ($value != @end($values)){
							$s .= $tabs."\tcase ".$value.";".PHP_EOL;
						} else {
							$s .= $tabs."\tcase ".$value.";";
						}
					}
					return $tabs.'enum '.$name." {\n".$s."\n".$tabs."}";
				},
			$content);
		}
		if (preg_match('/^(\t*|\s*)enum (?<name>\w+): (?<type>\w+) -> (?<value>\{.*\});?$/m', $content) != false){
			$content = preg_replace_callback(
				'/^(\t*|\s*)enum (?<name>\w+): (?<type>\w+) -> (?<value>\{.*\});?$/m',
				function ($matches) use ($types) {
					$tabs = $matches[1];
					$name = $matches['name'];
					$type = $matches['type'];
					$value = substr($matches['value'], 1, -1);
					eval('$values = ['.$value.'];');
					$s = '';
					foreach ($values as $value){
						if (in_array($type, $types) && call_user_func('is_'.$type, $value)){
							$method = match ($type) {
								'string' => fn() => $tabs."\tcase ".$value." = '".$value."';"
							};
							if ($value != @end($values)){
								$s .= $method().PHP_EOL;
							} else {
								$s .= $method();
							}
						}
					}
					return $tabs.'enum '.$name.': '.$type." {\n".$s."\n".$tabs."}";
				},
			$content);
		} else {
			// $this->log(__CLASS__, 'enum $name: $type -> {$value}; not found');
		}
		if (preg_match('/^(\t*|\s*)enum (?<name>\w+) -> (?<value>\{.*\});?$/m', $content) != false){
			$content = preg_replace_callback(
				'/^(\t*|\s*)enum (?<name>\w+) -> (?<value>\{.*\});?$/m',
				function ($matches) {
					$tabs = $matches[1];
					$name = $matches['name'];
					$value = substr($matches['value'], 1, -1);
					eval('$values = ['.$value.'];');
					$s = '';
					foreach ($values as $value){
						if ($value != @end($values)){
							$s .= $tabs."\tcase ".$value." = '".$value."';".PHP_EOL;
						} else {
							$s .= $tabs."\tcase ".$value." = '".$value."';";
						}
					}
					return $tabs.'enum '.$name." {\n".$s."\n".$tabs."}";
				},
			$content);
		} else {
			// $this->log(__CLASS__, 'enum $name -> {$value}; not found');
		}
		return $content;
	}

}
<?php

# Сделать @JhpDocBlock
# убрать Loader из GLOBALS
# Перенести модули

$loader = require_once 'vendor/autoload.php';

use Weird\Translator\TranslatorFactory;

define('ROOT', __DIR__);

$req = [
	'terminal_manager',
	'Colors',
	'arr'
];
array_map(
	fn($f) => require __DIR__.'/req/'.$f.'.php',
	array: $req
);

$manager = new terminal_manager('jhp', $argv, false);

$manager->get_help_list = function () use ($manager) {
	$commands = array_merge($manager->command_list, $manager->on_list);
	$options = [
		'-h',
		'--help'
	];
	return [$commands, $options];
};

$manager->stabilize = function (string $pattern, $names, $spaces = 1) use ($manager) {
	$List = [];
	$newList = arr::blob_string_sort($names);
	$spacesBefore = $spaces;
	for ($i = 0; $i < count($newList); $i++){
		if ($i > 0) {
			$before = $newList[$i - 1];
			$name = $newList[$i];
		} else {
			$before = '';
			$name = $newList[$i];
			goto add;
		}
		if (array_key_exists($name, $manager->descriptions) && array_key_exists($before, $manager->descriptions)){
			add:
			if ($i > 0){
				if (strlen($newList[$i - 1]) > strlen($name)){
					$spaces += (strlen($newList[$i - 1]) - strlen($name));
				}
			}
			$share = strlen($name) + $spaces;
			$shares[] = $share;
		}
	}
	$first = 0;
	$spaces = $spacesBefore;
	for ($i = 0; $i < count($newList); $i++){
		if ($i > 0) {
			$before = $newList[$i - 1];
			$name = $newList[$i];
		} else {
			$before = '';
			$name = $newList[$i];
			goto add_2;
		}
		if (array_key_exists($name, $manager->descriptions) && array_key_exists($before, $manager->descriptions)){
			add_2:
			if ($i > 0){
				if (strlen($newList[$i - 1]) > strlen($name)){
					$spaces += (strlen($newList[$i - 1]) - strlen($name));
				} else {
					if (count(array_unique($shares)) > 1){
						if ($first == 0){
							$spaces += 1;
							$first++;
						}
					}
				}
			}
			$newpattern = strtr($pattern, [
				'%name' => Colors::setColor($name, 'brown'),
				'%s' => str_repeat(' ', $spaces),
				// '%option' => '',
				'%desc' => Colors::setColor($manager->getDescription($name), '')
			]);
			$List[] = $newpattern;
		}
	}
	return $List;
};

$manager->command('init', function ($two_arg) {
	echo Colors::setColor('init', 'cyan') . ' config...' . PHP_EOL;
})->setDescription('init', 'init config for folder');

$manager->command('run', function ($path_to_file) {
	if (strlen($path_to_file) == 0) $path_to_file = 'view/example1.jhp';
	echo Colors::setColor('handling...', 'cyan') . ' "'.Colors::setColor($path_to_file, 'green').'"' . PHP_EOL;
	$translator = TranslatorFactory::make();
	$translator->render($path_to_file);
})->setDescription('run', 'run project compile');

$manager->command(['-h', '--help'], function ($module_name, $json, $json_path) use (&$manager) {
	$all_list = array_merge($manager->command_list, $manager->on_list);
	// nl $name.'x manager for '.$name . PHP_EOL;
	echo Colors::setColor($manager->name, 'cyan').Colors::setColor(' <command> [options]', 'light_gray') . PHP_EOL;
	echo PHP_EOL;
	$all = [
		'run' => '<path_to_file>'
	];
	$texts = $manager->stabilize('- %name %option %s%desc', $all_list);
	// var_dump($texts);
	foreach ($texts as $text) {
		foreach ($all as $command => $option) {
			if (str_contains($text, '- '.Colors::setColor($command, 'brown'))){
				$text = strtr($text, [
					'%option' => ' '.Colors::setColor($option, 'cyan')
				]);
				echo $text . PHP_EOL;
			} else {
				$text = strtr($text, [
					'%option' => str_repeat(' ', strlen($option) + 1)
				]);
				echo $text . PHP_EOL;
			}
		}
	}
});

$manager->other(function ($manager_name, $args) use ($manager) {
	if (count($args) > 1){
		$command = $manager_name.' '.implode(' ', $args);
	} else {
		$command = $manager_name.' '.$args[1];
	}
	list($commands, $options) = $manager->get_help_list();
	$com = '';
	for ($i = 1; $i <= count($args); $i++){
		if (!str_starts_with($args[$i], '-')){
			$com = $args[$i];
			break;
		}
	}
	$option_found = false;
	$current_options = [];
	for ($i = 1; $i <= count($args); $i++){
		$option = $args[$i];
		if (str_starts_with($option, '-') && in_array($option, $options)){
			$option_found = true;
			$current_options[] = $option;
		}
	}
	// print_r($commands);
	// print_r($options);
	// print_r($current_options);
	if (count($current_options) == 1){
		$option = $current_options[0];
	} else {
		$option = implode(' ', $current_options);
	}
	if (strlen($com) > 0 && in_array($com, $commands)){
		if ($option_found){
			system($command);
			// echo $command . ' (merged)' . PHP_EOL;
			// echo $com . ' (com)' . PHP_EOL;
			// echo $option . ' (option)' . PHP_EOL;
		} else {
			system($command);
			// echo $command . ' (command only)' . PHP_EOL;
		}
	} else {
		if (strlen($com) > 0 && !in_array($com, $commands)){
			echo 'argument "'.$com.'" not found' . PHP_EOL;
		} else {
			if ($option_found){
				// system($command);
				// echo $command . ' (option only)' . PHP_EOL;
			}
		}
	}
	// system($command);
});
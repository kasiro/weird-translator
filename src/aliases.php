<?php

$requireMethod = 'auto';

switch ($requireMethod) {
	case 'manual':
		return [
			'fn' => Weird\Translator\Modules\FnModule::class,
			'import' => Weird\Translator\Modules\ImportModule::class,
		];
	
	case 'auto':
		$prefix = 'Weird\\Translator\\Modules\\';
		$aliases = [];
		foreach (glob(__DIR__.'/Modules/*.php') as $path){
			$base = basename($path);
			$name = explode('.', $base)[0];
			$aliasName = str_contains($name, 'Module') ? strtolower(
				str_replace('Module', '', $name)
			) : null;
			$aliases[$aliasName] = $prefix . $name;
		}
		return $aliases;
	
	case 'hybrid':
		$aliases = [];
		$aliases = array_merge($aliases, [
			'main' => Weird\Translator\MainModules\MainModule::class,
		]);
		return $aliases;
}
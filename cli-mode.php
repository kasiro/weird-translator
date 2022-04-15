<?php

# Убрать loader из GLOBALS
# Перенести Модули

use Weird\Translator\TranslatorFactory;

$loader = require_once 'vendor/autoload.php';

if (isset($argv[1])) $path = $argv[1];
else $path = 'view/example1.jhp';

$GLOBALS['loader'] = $loader;

define('ROOT', __DIR__);

$translator = TranslatorFactory::make();

$translator->render($path);
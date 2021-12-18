<?php

use Weird\Translator\TranslatorFactory;

require_once 'vendor/autoload.php';

define('ROOT', __DIR__);

$translator = TranslatorFactory::make();

$translator->render('/view/example1.jhp');
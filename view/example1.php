<?php

/**
 * @jhpdoc
 */



use Weird\Translator\Modules\*;
class Main {

	public static string $name = 'Vasya';
	private $value = 'hehe';
	private static $p = 'yes';

	public function add(){
		$name = @'Danya' ? 'Danya' : (
			@$mate ? $mate : null
		);
	}

	public static function func(){
		enum sec: string {
			case black = 'black';
			case dark_gray = 'dark_gray';
			case blue = 'blue';
		}
	}
}

$main->add('text', function () {

}, $main);
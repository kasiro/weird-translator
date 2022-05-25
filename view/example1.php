<?php
/**
 *? @status parse
 ** @module CommentsModule {}
 ** @module EnumModule {}
 ** @module FnModule {}
 ** @module ImportModule {}
 ** @module JsClassModule {}
 ** @module JsElseModule {}
 ** @module NewLineModule {}
 ** @module QuantifersModule {}
 ** @module UseModule {muxuse => false}
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
		enum fcolors: string {
			case black = 'black';
			case dark_gray = 'dark_gray';
			case blue = 'blue';
		}
	}
}

$main->add('text', function () {

}, $main);
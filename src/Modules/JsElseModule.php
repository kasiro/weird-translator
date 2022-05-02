<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleInterface;
use Weird\Translator\Contracts\ModuleExtends;

final class JsElseModule extends ModuleExtends implements ModuleInterface {

	public function getHash($len){
		$end_string = '';
		$string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		for ($i = 1; $i <= $len; $i++) {
			$end_string .= $string[mt_rand(0, strlen($string) - 1)];
		}
		return '$%' . $end_string;
	}

	public function ternarn_else($arr, $tabs, $i, $end_string = '', $hash = '', $mini = true){
		if (!$mini) {
			$end = ($i + 1 < count($arr)) ? '('."\n".str_repeat("\t", $i+1).$tabs.'@' . $arr[$i + 1] . ' ? ' . $arr[$i + 1] . ' : ' . $hash . "\n" . $tabs .str_repeat("\t", $i) . ')' : 'null';
		} else {
			$end = ($i + 1 < count($arr)) ? '(@' . $arr[$i + 1] . ' ? ' . $arr[$i + 1] . ' : ' . $hash . ')' : 'null';
		}
		if ($end == 'null') return str_replace($hash, 'null', $end_string);
		if ($i == 0) $string = $arr[$i] . ' ? ' . $arr[$i] . ' : ' . $hash;
		else $string = '(@' . $arr[$i] . ' ? ' . $arr[$i] . ' : ' . $hash .')';
		// echo $end . "\n";
		if ($i == 0) $end_string .= $string;
		$end_string = str_replace($hash, $end, $end_string);
		$i++;
		$end_string = $this->ternarn_else($arr, $tabs, $i, $end_string, $hash, $mini);
		return $end_string;
	}

	public function process(string $content): string {
		$regexp = '/^(\t*|\s*)(\$.*) = (.*)( \| )(.*?);/m';
		$content = preg_replace_callback($regexp, function ($matches) {
			$settings = ['mini' => false];
			$tabs = $matches[1];
			$res = '';
			for ($i = 3; $i < count($matches); $i++) {
				$res .= $matches[$i];
			}
			$arr = explode(' | ', $res);
			$hash = $this->getHash(10);
			if ($settings['mini']) {
				$s = $this->ternarn_else($arr, $tabs, 0, hash: $hash);
			} else {
				$s = $this->ternarn_else($arr, $tabs, 0, hash: $hash, mini: false);
			}
			$s .= ';';
			return $tabs.$matches[2] . ' = @' . $s;
		}, $content);
		return $content;
	}

}
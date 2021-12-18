<?php

namespace Weird\Translator;

final class TranslatorFactory
{
	public static function make()
	{
		return new Translator();
	}
}
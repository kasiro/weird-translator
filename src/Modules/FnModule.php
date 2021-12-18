<?php

namespace Weird\Translator\Modules;

use Weird\Translator\Contracts\ModuleInterface;

final class FnModule implements ModuleInterface
{
	/**
	 * Тут мы обрабатываем файл который нам дан и возвращаем в обработанном ввиде, и так этот файл пройдет по модулям
	 * обрабатываясь и в конце возвращает результат
	*/
	public function process(string $content): string
	{
		$content = $content . PHP_EOL. '< Тут сработал модуль fn >';

		return $content;
	}
}
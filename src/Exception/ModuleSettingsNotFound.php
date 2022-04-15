<?php

namespace Weird\Translator\Exception;

final class ModuleSettingsNotFoundException extends \Exception
{
	public string $name;

	public function __construct(string $name, $code = 0, \Throwable $previous = null)
	{
		parent::__construct('Settings not found', $code, $previous);
		$this->name = $name;
	}

}
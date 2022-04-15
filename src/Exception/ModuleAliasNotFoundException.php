<?php

namespace Weird\Translator\Exception;

final class ModuleAliasNotFoundException extends \Exception
{
	public string $name;

	public function __construct(string $name, $code = 0, \Throwable $previous = null)
	{
		parent::__construct('Alias not found', $code, $previous);
		$this->name = $name;
	}

}
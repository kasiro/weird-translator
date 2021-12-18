<?php

namespace Weird\Translator\Contracts;

interface ModuleInterface
{
	public function process(string $content): string;

}
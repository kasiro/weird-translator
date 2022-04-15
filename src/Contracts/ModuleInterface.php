<?php

namespace Weird\Translator\Contracts;

interface ModuleInterface {
	public function process(string $content): string;
	// public function setSettings(array $settings): void;
}
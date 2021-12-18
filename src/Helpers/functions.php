<?php

namespace Weird\Translator;

use function PHPUnit\Framework\fileExists;

function ob_file_get(string $file)
{
	if (!fileExists($file)) {
		throw new \RuntimeException('File not found:' . $file);
	}

	ob_start();
		require $file;
	return ob_get_clean();
}
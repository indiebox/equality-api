<?php

namespace App\Support\Linting\Linters;

use Tighten\TLint\Linters\NewLineAtEndOfFile as BaseLinter;

class NewLineAtEndOfBladeFile extends BaseLinter
{
    public const DESCRIPTION = 'Blade file should end with a new line.';

    public static function appliesToPath(string $path, array $configPaths): bool
    {
        return str_ends_with($path, "blade.php");
    }
}

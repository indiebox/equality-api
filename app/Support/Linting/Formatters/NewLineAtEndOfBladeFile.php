<?php

namespace App\Support\Linting\Formatters;

use Tighten\TLint\Formatters\NewLineAtEndOfFile as BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class NewLineAtEndOfBladeFile extends BaseFormatter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Applies a newline at the end of blade files.';
}

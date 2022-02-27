<?php

namespace App\Support\Linting\Formatters;

use PhpParser\Lexer;
use PhpParser\Parser;
use Tighten\TLint\BaseFormatter;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class NoWhitespaceAtEndOfLine extends BaseFormatter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Trim trailing whitespaces.';

    public function format(Parser $parser, Lexer $lexer)
    {
        // We are using "\h+" for find only whitespaces(exclude EOL).
        // Also we use "$1" to get the EOL of the string that stood before, so as not to create false files changes.
        $this->code = preg_replace(
            '/\h+(\r\n|\n|\r)$/m',
            "$1",
            $this->code,
            -1,
            $count
        );

        return $this->code;
    }
}

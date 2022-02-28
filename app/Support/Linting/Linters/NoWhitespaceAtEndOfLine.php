<?php

namespace App\Support\Linting\Linters;

use PhpParser\Parser;
use Tighten\TLint\BaseLinter;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\Concerns\LintsBladeTemplates;

class NoWhitespaceAtEndOfLine extends BaseLinter
{
    use LintsBladeTemplates;

    public const DESCRIPTION = 'Trailing whitespace found at end of line.';

    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            preg_match(
                '/\s{1,}$/m',
                $codeLine,
                $matches
            );

            if (count($matches) != 0) {
                $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
            }
        }

        return $foundNodes;
    }
}

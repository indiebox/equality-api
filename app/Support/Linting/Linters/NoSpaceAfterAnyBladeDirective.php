<?php

namespace App\Support\Linting\Linters;

use PhpParser\Parser;
use Tighten\TLint\CustomNode;
use Tighten\TLint\Linters\NoSpaceAfterBladeDirectives as BaseLinter;

class NoSpaceAfterAnyBladeDirective extends BaseLinter
{
    public function lint(Parser $parser)
    {
        $foundNodes = [];

        foreach ($this->getCodeLines() as $line => $codeLine) {
            $matches = [];

            // https://github.com/illuminate/view/blob/fd894237a3d793eca55bc71ff6317fae1bfe1856/Compilers/BladeCompiler.php#L427
            preg_match(
                '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
                $codeLine,
                $matches
            );

            if (
                ($matches[1] ?? null) != null
                && ($matches[2] ?? null) !== ''
                // We find directives like: @if (), @section (), etc.
                && mb_substr($codeLine, mb_strpos($codeLine, $matches[1]) + mb_strlen($matches[1]), 2) == ' ('
            ) {
                $foundNodes[] = new CustomNode(['startLine' => $line + 1]);
            }
        }

        return $foundNodes;
    }
}

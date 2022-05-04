<?php

namespace App\Support\Linting;

use App\Support\Linting\Formatters as CustomFormatters;
use App\Support\Linting\Linters as CustomLinters;
use Tighten\TLint\Formatters;
use Tighten\TLint\Linters;
use Tighten\TLint\Presets\PresetInterface;

class LaravelPreset implements PresetInterface
{
    public function getLinters(): array
    {
        return [
            // General Php
            Linters\NoUnusedImports::class,
            Linters\NoStringInterpolationWithoutBraces::class,
            Linters\OneLineBetweenClassVisibilityChanges::class,
            Linters\TrailingCommasOnArrays::class,

            // Laravel related
            Linters\ArrayParametersOverViewWith::class,
            Linters\FullyQualifiedFacades::class,
            Linters\NoCompact::class,
            Linters\NoDump::class,
            Linters\RequestValidation::class,
            Linters\RestControllersMethodOrder::class,
            Linters\UseAuthHelperOverFacade::class,
            Linters\UseConfigOverEnv::class,
            Linters\UseAnonymousMigrations::class,

            // Blade related
            Linters\SpacesAroundBladeRenderContent::class,
            CustomLinters\NoSpaceAfterAnyBladeDirective::class,
            CustomLinters\NoWhitespaceAtEndOfLine::class,
            CustomLinters\NewLineAtEndOfBladeFile::class,

            // Enable if project code-style will be changed to this behaviour
            // Linters\ApplyMiddlewareInRoutes::class,
        ];
    }

    public function getFormatters(): array
    {
        return [
            // General Php
            Formatters\UnusedImports::class,
            Formatters\ExcessSpaceBetweenAndAfterImports::class,

            // Laravel related
            Formatters\FullyQualifiedFacades::class,
            Formatters\UseAnonymousMigrations::class,

            // Blade related
            CustomFormatters\NewLineAtEndOfBladeFile::class,
            CustomFormatters\NoWhitespaceAtEndOfLine::class,
        ];
    }
}

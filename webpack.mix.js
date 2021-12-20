const mix = require('laravel-mix');
mix.disableNotifications();

/*
|-------------------------------------------------------------
| Main
|-------------------------------------------------------------
*/

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css/style.css', null, [
        require('tailwindcss'),
    ]);

// Extract vendor to separate file.
mix.extract();

/*
|-------------------------------------------------------------
| Production mode
|-------------------------------------------------------------
*/

if (mix.inProduction()) {
    mix.version();
}


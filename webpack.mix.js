let mix = require('laravel-mix');

if (mix.inProduction()) {
    mix.js('resources/js/katra.js', 'public/js/katra.min.js')
        .postCss('resources/css/katra.css', 'public/css/katra.min.css', [
            require("tailwindcss"),
        ]);
} else {
    mix.js('resources/js/katra.js', 'public/js/katra.js')
        .postCss('resources/css/katra.css', 'public/css/katra.css', [
            require("tailwindcss"),
        ]);
}



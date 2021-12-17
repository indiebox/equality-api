const plugin = require('tailwindcss/plugin');
const colors = require('tailwindcss/colors');

module.exports = {
    content: [
        './resources/**/*.{blade.php,js}',
    ],

    theme: {
        extend: {
            colors: {
                // Aliases.
                green: colors.emerald,
                yellow: colors.amber,
                purple: colors.violet,
            },

            border: {
                DEFAULT: '1px',
                '1': '1px',
                '3': '3px',
                '5': '5px',
                '7': '7px',
                '9': '9px',
            },

            width: {
                '1/10': '10%',
                '2/10': '20%',
                '3/10': '30%',
                '4/10': '40%',
                '5/10': '50%',
                '6/10': '60%',
                '7/10': '70%',
                '8/10': '80%',
                '9/10': '90%',
            },

            transitionTimingFunction: {
                'ease': 'ease',
            }
        },
    }
};

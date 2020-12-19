module.exports = {
  purge: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  darkMode: 'media',
  theme: {
    extend: {
      colors: {
        'primary': 'var(--primary-color)',
        'slate-900': '#151515',
        'slate-800': '#252525',
        'slate-700': '#353535',
        'slate-600': '#555555',
        'slate-500': '#757575',
        'slate-400': '#959595',
        'slate-300': '#A5A5A5',
        'slate-200': '#C5C5C5',
        'slate-100': '#E5E5E5',
        'slate-50':  '#F5F5F5',
      },
      brightness: {
        90: '.90',
        85: '.85',
      }
    },
  },
  variants: {
    extend: {},
  },
  plugins: [
    require('tailwindcss-css-filters'),
  ],
}

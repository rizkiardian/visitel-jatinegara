/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./vendor/filament/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        telkom: {
          DEFAULT: '#EE2E24',
          50: '#FDF2F2',
          100: '#FDE8E8',
          500: '#EE2E24',
          600: '#E02424',
          700: '#C81E1E',
          800: '#9B1C1C',
          900: '#771D1D',
        },
      },
    },
  },
  plugins: [],
}

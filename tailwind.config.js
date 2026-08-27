/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        cobalto: '#2563EB',
        marino: '#1E3A8A',
        blanco: '#FFFFFF',
        'gris-frio': '#F8FAFC',
        'slate-oscuro': '#0F172A',
      }
    },
  },
  plugins: [],
}
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.ts",
    "./resources/**/*.jsx",
    "./resources/**/*.tsx",
    "./resources/**/*.vue",
    "./app/Filament/**/*.php",
    "./app/View/Components/**/*.php",
    "./storage/framework/views/*.php",
  ],
  theme: {
    extend: {
        colors: {
            danger: colors.rose,
            primary: colors.blue,
            success: colors.green,
            warning: colors.yellow,
        },
    },
  },
  plugins: [],
}

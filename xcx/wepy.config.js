module.exports = {
  eslint: false,
  wpyExt: '.wpy',
  compilers: {
    sass: {
      outputStyle: 'compressed'
    },
    babel: {
      sourceMap: false,
      presets: ['es2015', 'stage-1'],
      plugins: ['transform-export-extensions', 'syntax-export-extensions']
    }
  }
}

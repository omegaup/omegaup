module.exports = {
  presets: [
    [
      '@babel/preset-env',
      {
        corejs: 3,
        modules: false,
        useBuiltIns: 'entry',
        targets: 'supports es6',
      },
    ],
  ],
  plugins: ['@babel/plugin-transform-async-to-generator'],
};

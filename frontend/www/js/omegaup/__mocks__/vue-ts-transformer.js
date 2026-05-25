/**
 * TypeScript transformer shim for vue-jest in Vue 3 compat mode.
 * This wraps babel-jest to handle .ts blocks inside .vue SFCs.
 */
const babelJest = require('babel-jest').default;

const transformer = babelJest.createTransformer({
  presets: [
    [
      '@babel/preset-env',
      {
        corejs: 3,
        modules: false,
        useBuiltIns: 'entry',
        targets: {
          esmodules: false,
          node: 'current',
        },
      },
    ],
  ],
  plugins: [
    ['@babel/plugin-proposal-decorators', { legacy: true }],
    '@babel/plugin-transform-async-to-generator',
    '@babel/plugin-transform-modules-commonjs',
    ['@babel/plugin-proposal-class-properties', { loose: true }],
    ['@babel/plugin-transform-typescript', { allowNamespaces: true }],
  ],
});

module.exports = {
  process(sourceText, sourcePath, options) {
    return transformer.process(sourceText, sourcePath, options);
  },
};

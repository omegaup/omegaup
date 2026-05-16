/**
 * Custom TypeScript transformer for vue-jest that uses Babel instead of
 * TypeScript's transpileModule. This preserves ES6 classes so that
 * Babel-transpiled class components can extend @vue/compat's ES6 Vue class.
 */
const { transform } = require('@babel/core');

module.exports = {
  process(scriptContent, filePath, config) {
    const babelOptions = {
      filename: filePath,
      presets: [
        [
          '@babel/preset-env',
          {
            targets: { node: 'current' },
            modules: 'commonjs',
          },
        ],
      ],
      plugins: [
        ['@babel/plugin-proposal-decorators', { legacy: true }],
        ['@babel/plugin-proposal-class-properties', { loose: true }],
        ['@babel/plugin-transform-typescript', { allowNamespaces: true }],
      ],
      sourceMaps: true,
    };

    const result = transform(scriptContent, babelOptions);

    return {
      code: result.code + '\n',
      map: result.map,
    };
  },
};

const babelConfig = {
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
};

module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'ts', 'vue'],
  moduleDirectories: ['node_modules'],
  moduleNameMapper: {
    '^vue$': '@vue/compat',
    'vue-async-computed-decorator':
      '<rootDir>/node_modules/vue-async-computed-decorator/dist/index.js',
    '^@/(.*)$': '<rootDir>/frontend/www/$1',
    '\\.(css|less)$':
      '<rootDir>/frontend/www/js/omegaup/__mocks__/styleMock.js',
    'monaco-editor':
      '<rootDir>/frontend/www/third_party/js/__mocks__/monacoEditor.js',
    sugar: '<rootDir>/frontend/www/js/omegaup/__mocks__/sugar.js',
    'vue-codemirror-lite':
      '<rootDir>/node_modules/vue-codemirror-lite/dist/vuecodemirror.min.js',
    '@voerro/vue-tagsinput':
      '<rootDir>/node_modules/@voerro/vue-tagsinput/dist/voerro-vue-tagsinput.js',
    '^bootstrap-vue$': '<rootDir>/frontend/www/js/omegaup/__mocks__/bootstrap-vue.ts',
  },
  setupFilesAfterEnv: ['<rootDir>/frontend/www/js/omegaup/test.setup.ts'],
  transform: {
    '.*\\.vue$': [
      '<rootDir>/frontend/www/js/omegaup/__mocks__/vue3-jest-wrapper.js',
      {
        babelConfig,
      },
    ],
    '.*\\.[jt]sx?$': ['babel-jest', babelConfig],
  },
  transformIgnorePatterns: [
    'node_modules/(?!(vue-.*|@vue/.*|@voerro/vue-tagsinput|monaco-editor|monaco-editor-core|vue-facing-decorator)/)',
  ],
  globals: {
    'vue-jest': {
      transform: {
        ts: './frontend/www/js/omegaup/__mocks__/vue-ts-transformer.js',
      },
    },
  },
  testEnvironmentOptions: {
    url: 'http://localhost:8001/',
  },
};

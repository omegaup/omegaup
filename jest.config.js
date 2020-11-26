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
    '@babel/plugin-transform-async-to-generator',
    '@babel/plugin-transform-modules-commonjs',
    '@babel/plugin-proposal-class-properties',
    ['@babel/plugin-transform-typescript', { allowNamespaces: true }],
  ],
};

module.exports = {
  testEnvironment: 'jsdom',
  moduleFileExtensions: ['js', 'ts', 'vue'],
  moduleDirectories: ['node_modules'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/frontend/www/$1',
    '\\.(css|less)$':
      '<rootDir>/frontend/www/js/omegaup/__mocks__/styleMock.js',
  },
  setupFiles: ['<rootDir>/frontend/www/js/omegaup/test.setup.js'],
  globals: {
    'vue-jest': {
      babelConfig,
    },
  },
  transform: {
    '.*\\.vue$': [
      'vue-jest',
      {
        babelConfig,
      },
    ],
    '.*\\.[jt]sx?$': ['babel-jest', babelConfig],
  },
  transformIgnorePatterns: ['node_modules/(?!(vue-.*|@voerro/vue-tagsinput)/)'],
  testURL: 'http://localhost:8001/',
};

module.exports = {
  root: true,
  parser: 'vue-eslint-parser',
  parserOptions: {
    parser: '@typescript-eslint/parser',
  },
  env: {
    jquery: true,
    node: true,
  },
  plugins: ['@typescript-eslint'],
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:vue/vue3-recommended',
    'prettier',
    'prettier/@typescript-eslint',
    'prettier/vue',
  ],
  rules: {
    // Disabling this rule since inferrable types are still useful to declare
    // for humans.
    '@typescript-eslint/no-inferrable-types': 'off',

    // This rule is just annoying, since it prevents (legitimate) no-op
    // functions from being defined.
    '@typescript-eslint/no-empty-function': 'off',

    // TODO: Remove when we migrate to Vue 3, since this syntax doesn't _quite_
    // work all the time in Vue 2.
    'vue/no-deprecated-v-bind-sync': 'off',

    // TODO(#4626): Remove all uses of v-html.
    'vue/no-v-html': 'off',

    // TODO(#4778): Add key to ALL v-for.
    'vue/require-v-for-key': 'off',

    // TODO: Remove all these exceptions.
    '@typescript-eslint/ban-ts-comment': 'off',
    '@typescript-eslint/explicit-module-boundary-types': 'off',
    '@typescript-eslint/no-explicit-any': 'off',
    '@typescript-eslint/no-namespace': 'off',
  },
};

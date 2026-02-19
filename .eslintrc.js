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
  plugins: ['@typescript-eslint', 'jest-dom', 'simple-import-sort'],
  extends: [
    'eslint:recommended',
    'plugin:@typescript-eslint/recommended',
    'plugin:jest-dom/recommended',
    // Vue 2 ruleset. Do NOT mix with vue3-* until we migrate.
    'plugin:vue/recommended',
    // TODO: when migrating to Vue 3, replace the line above with vue3-recommended
    'prettier',
    'prettier/@typescript-eslint',
    'prettier/vue',
  ],
  rules: {
    // Enforce sorted imports and exports for consistency and reduced merge conflicts.
    'simple-import-sort/imports': 'error',
    'simple-import-sort/exports': 'error',

    // Disabling this rule since inferrable types are still useful to declare
    // for humans.
    '@typescript-eslint/no-inferrable-types': 'off',

    // This rule is just annoying, since it prevents (legitimate) no-op
    // functions from being defined.
    '@typescript-eslint/no-empty-function': 'off',

    // Avoid using bracket type assertions, since those are the old syntax.
    '@typescript-eslint/consistent-type-assertions': 'error',

    // TODO: Remove when we migrate to Vue 3, since this syntax doesn't _quite_
    // work all the time in Vue 2.
    'vue/no-deprecated-v-bind-sync': 'off',

    // TODO(#4778): Add key to ALL v-for.
    'vue/require-v-for-key': 'off',

    // TODO: Remove when we migrate to Vue 3, beacause of
    // https://v3.vuejs.org/guide/migration/key-attribute.html#with-template-v-for
    'vue/no-v-for-template-key-on-child': 'off',

    // TODO: Remove all these exceptions.
    '@typescript-eslint/explicit-module-boundary-types': 'off',
    '@typescript-eslint/no-explicit-any': 'off',
    '@typescript-eslint/no-namespace': 'off',
  },
};

/**
 * Custom Jest transformer for Vue 3 SFCs that wraps @vue/vue3-jest
 * and adds special export handling for vue-facing-decorator,
 * similar to the built-in handling for vue-property-decorator.
 */
const vue3Jest = require('@vue/vue3-jest');

module.exports = {
  process(sourceText, sourcePath, options) {
    const result = vue3Jest.process(sourceText, sourcePath, options);

    let code;
    if (typeof result === 'string') {
      code = result;
    } else {
      code = result.code;
    }

    if (code && code.includes('vue-facing-decorator')) {
      const append = `
const __vccOpts = exports.default.__vccOpts;
if (__vccOpts) {
  delete __vccOpts.__vfdConstructor;
  exports.default = { ...__vccOpts, render: exports.default.render };
}
`;
      if (typeof result === 'string') {
        return code + append;
      }
      result.code = code + append;
    }

    return result;
  },
};

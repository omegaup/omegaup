// diff_match_patch is not a well-behaved module, since it tries to modify
// `this` (instead of `global` or `window`, which it shouldn't do regardless).
// So, by requiring, it will cause a runtime error since `this` is not defined
// in that context.
// We need to mock it to avoid requiring it.

function diff_match_patch() {}
diff_match_patch.prototype.diff_main = function() {
  return [];
};

global.diff_match_patch = diff_match_patch;
global.DIFF_DELETE = -1;
global.DIFF_INSERT = 1;
global.DIFF_EQUAL = 0;

module.exports = {
  diff_match_patch,
  DIFF_DELETE: -1,
  DIFF_INSERT: 1,
  DIFF_EQUAL: 0,
};

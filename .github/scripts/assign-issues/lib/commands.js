function escapeRegex(s) {
  return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function hasToken(body, token) {
  // Token should match as a separate word-ish unit.
  const re = new RegExp(`(^|\\s)${escapeRegex(token)}(\\s|$)`, 'i');
  return re.test(String(body || ''));
}

function parseCommand(body, { assignCmd = '/assign', unassignCmd = '/unassign' } = {}) {
  if (hasToken(body, unassignCmd)) return { kind: 'unassign' };
  if (hasToken(body, assignCmd)) return { kind: 'assign' };
  return { kind: 'none' };
}

module.exports = { parseCommand };

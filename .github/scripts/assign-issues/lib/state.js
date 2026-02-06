const STATE_PREFIX = 'omegaup-assign-bot:state';

function formatStateComment(state) {
  return `<!-- ${STATE_PREFIX} ${JSON.stringify(state)} -->`;
}

function defaultState() {
  return { v: 1, assignedAt: {}, blockedReassign: {}, suggested: false };
}

function tryParseStateFromBody(body) {
  const text = String(body || '');
  const re = new RegExp(`<!--\\s*${STATE_PREFIX}\\s*([\\s\\S]*?)\\s*-->`, 'm');
  const m = text.match(re);
  if (!m) return null;
  const raw = m[1].trim();
  if (!raw) return null;
  try {
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

function parseStateFromBody(body) {
  return tryParseStateFromBody(body) || defaultState();
}

module.exports = {
  formatStateComment,
  tryParseStateFromBody,
  parseStateFromBody,
  defaultState,
  STATE_PREFIX,
};

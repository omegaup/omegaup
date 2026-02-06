function normalizeLogin(raw) {
  const v = String(raw || '').trim();
  if (!v) return '';
  return v.startsWith('@') ? v.slice(1) : v;
}

function escapeQuotes(s) {
  return String(s).replace(/"/g, '\\"');
}

function formatQualifierValue(raw) {
  const v = normalizeLogin(raw);
  if (!v) return '';
  // Most GitHub usernames match this. For anything else (e.g. github-actions[bot]),
  // wrap in quotes to avoid search syntax errors.
  if (/^[A-Za-z0-9-]+$/.test(v)) return v;
  return `"${escapeQuotes(v)}"`;
}

module.exports = { normalizeLogin, formatQualifierValue };


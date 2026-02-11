const { parseCommand } = require('./commands');

const DEFAULT_PATTERNS = [
  /\bplease\s+assign\s+.*\bme\b/i,
  /\bassign\s+(this\s+)?issue\s+to\s+me\b/i,
  /\bi\s+would\s+like\s+to\s+work\s+on\s+this\b/i,
  /\bcan\s+i\s+(take|work\s+on)\s+this\b/i,
  /\bi\s+want\s+to\s+work\s+on\s+this\b/i,
];

function shouldSuggestAssign(body, patterns = DEFAULT_PATTERNS) {
  const text = String(body || '');
  if (parseCommand(text).kind !== 'none') return false;
  return patterns.some((re) => re.test(text));
}

module.exports = { shouldSuggestAssign, DEFAULT_PATTERNS };


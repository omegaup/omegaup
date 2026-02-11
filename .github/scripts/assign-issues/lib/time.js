function nowIso() {
  return new Date().toISOString();
}

function parseIso(iso) {
  const d = new Date(iso);
  return Number.isNaN(d.getTime()) ? null : d;
}

function msSince(iso, now = new Date()) {
  const d = parseIso(iso);
  if (!d) return null;
  return now.getTime() - d.getTime();
}

function daysToMs(days) {
  return Number(days) * 24 * 60 * 60 * 1000;
}

module.exports = { nowIso, parseIso, msSince, daysToMs };


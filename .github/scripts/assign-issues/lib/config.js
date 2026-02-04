function envInt(name, fallback) {
  const raw = process.env[name];
  if (raw === undefined || raw === null || raw === '') return fallback;
  const n = Number(raw);
  return Number.isFinite(n) ? n : fallback;
}

function envBool(name, fallback) {
  const raw = process.env[name];
  if (raw === undefined || raw === null || raw === '') return fallback;
  return ['1', 'true', 'yes', 'y', 'on'].includes(String(raw).trim().toLowerCase());
}

function getConfig() {
  return {
    assignCmd: process.env.ASSIGN_CMD || '/assign',
    unassignCmd: process.env.UNASSIGN_CMD || '/unassign',

    // Limits
    maxAssignmentsPerUser: envInt('MAX_ASSIGNMENTS_PER_USER', 5),
    maxAssigneesPerIssue: envInt('MAX_ASSIGNEES_PER_ISSUE', 1),
    bypassMergedPrThreshold: envInt('BYPASS_MERGED_PR_THRESHOLD', 10),

    // Deadline
    deadlineDays: envInt('DEADLINE_DAYS', 7),

    // Features
    enableSuggestion: envBool('ENABLE_SUGGESTION', true),
  };
}

module.exports = { getConfig };


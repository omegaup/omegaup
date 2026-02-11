function maxAssignmentsMessage({ maxAssignmentsPerUser }) {
  return `You already have the maximum number of assigned issues (${maxAssignmentsPerUser}). Please wrap up or unassign one before taking another.`;
}

function blockedReassignMessage() {
  return `You were auto-unassigned due to inactivity (no PR within the deadline). Kindly ask the maintainers to assign this issue to you again.`;
}

function assignedMessage({ deadlineDays }) {
  return `Assigned! Please open a pull request within ${deadlineDays} days. If no PR is opened, this issue will be automatically unassigned to keep the queue moving.`;
}

function unassignedDueToDeadlineMessage({ deadlineDays }) {
  return `Unassigned because no pull request was opened within ${deadlineDays} days. If you want to be assigned to this issue again, kindly ask the maintainers to assign it to you again.`;
}

function suggestionMessage({ assignCmd, deadlineDays }) {
  return `If you'd like to take this issue, comment with ${assignCmd}. Please note the ${deadlineDays}-day PR expectation.`;
}

function issueMaxAssigneesMessage({ currentAssignees, maxAssigneesPerIssue }) {
  const list = (currentAssignees || []).map((u) => `@${u}`).join(', ');
  return `This issue already has the maximum number of assignees (${maxAssigneesPerIssue}): ${list || '(none)'}.`;
}

function rejectedAssignFooter() {
  return `This assignment was not applied. If you open a PR, it will be blocked until the issue is assigned to you.`;
}

module.exports = {
  maxAssignmentsMessage,
  blockedReassignMessage,
  assignedMessage,
  unassignedDueToDeadlineMessage,
  suggestionMessage,
  issueMaxAssigneesMessage,
  rejectedAssignFooter,
};

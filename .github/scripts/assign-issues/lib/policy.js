function decideAssign({
  requester,
  issueAuthor,
  currentAssignees,
  maxAssigneesPerIssue,
  requesterOpenAssignedIssuesCount,
  maxAssignmentsPerUser,
  requesterMergedPrCount,
  bypassMergedPrThreshold,
  isRequesterBlockedFromReassign,
}) {
  if (isRequesterBlockedFromReassign) {
    return { ok: false, reason: 'blocked_reassign' };
  }

  if (String(requester).toLowerCase() === String(issueAuthor).toLowerCase()) {
    // Issue author can always self-assign their own issue (except if blocked).
    return { ok: true };
  }

  const assignees = Array.isArray(currentAssignees) ? currentAssignees : [];
  const normalizedRequester = String(requester).toLowerCase();
  if (assignees.map((a) => String(a).toLowerCase()).includes(normalizedRequester)) {
    return { ok: true };
  }

  if (
    Number(maxAssigneesPerIssue) > 0 &&
    assignees.length >= Number(maxAssigneesPerIssue)
  ) {
    return { ok: false, reason: 'issue_max_assignees' };
  }

  if (Number(requesterMergedPrCount) >= Number(bypassMergedPrThreshold)) {
    return { ok: true };
  }

  if (Number(requesterOpenAssignedIssuesCount) >= Number(maxAssignmentsPerUser)) {
    return { ok: false, reason: 'user_max_assignments' };
  }

  return { ok: true };
}

module.exports = { decideAssign };


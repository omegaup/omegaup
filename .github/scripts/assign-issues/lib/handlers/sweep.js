const { listOpenIssuesWithAssignees } = require('../issues');
const { findIssueState } = require('../state-store');
const { msSince, daysToMs, nowIso } = require('../time');
const { withState } = require('../state-body');
const { removeIssueAssignees, createIssueComment } = require('../github');
const { hasLinkedPrByAuthorSince } = require('../prs');
const messages = require('../messages');

async function handleSweep({ github, context, config }) {
  const owner = context.repo.owner;
  const repo = context.repo.repo;

  const issues = await listOpenIssuesWithAssignees({ github, owner, repo });

  for (const item of issues) {
    // Search results include PRs too; guard anyway.
    if (item.pull_request) continue;

    const issueNumber = item.number;
    const currentAssignees = (item.assignees || []).map((a) => a.login);
    if (currentAssignees.length === 0) continue;

    // Only handle issues where our bot previously stored assignment timestamps.
    const state = await findIssueState({ github, owner, repo, issue_number: issueNumber });
    if (!state || !state.assignedAt) continue;

    const now = new Date();
    const deadlineMs = daysToMs(config.deadlineDays);

    for (const assignee of currentAssignees) {
      const assignedAt = state.assignedAt[assignee];
      if (!assignedAt) continue;

      const ageMs = msSince(assignedAt, now);
      if (ageMs === null || ageMs < deadlineMs) continue;

      const hasPr = await hasLinkedPrByAuthorSince({
        github,
        owner,
        repo,
        issueNumber,
        author: assignee,
        sinceIso: assignedAt,
      });
      if (hasPr) continue;

      await removeIssueAssignees({
        github,
        owner,
        repo,
        issue_number: issueNumber,
        assignees: [assignee],
      });

      state.blockedReassign = state.blockedReassign || {};
      state.blockedReassign[assignee] = nowIso();
      delete state.assignedAt[assignee];

      const msg = `@${assignee} ${messages.unassignedDueToDeadlineMessage({
        deadlineDays: config.deadlineDays,
      })}`;

      await createIssueComment({
        github,
        owner,
        repo,
        issue_number: issueNumber,
        body: withState(msg, state),
      });
    }
  }
}

module.exports = { handleSweep };


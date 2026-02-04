const { parseCommand } = require('../commands');
const { shouldSuggestAssign } = require('../suggest');
const { decideAssign } = require('../policy');
const { loadIssueState } = require('../state-store');
const { nowIso } = require('../time');
const { withState } = require('../state-body');
const {
  createIssueComment,
  addIssueAssignees,
  removeIssueAssignees,
} = require('../github');
const { getMergedPrCount } = require('../prs');
const { getOpenAssignedIssuesCount } = require('../issues');
const messages = require('../messages');

async function handleIssueComment({ github, context, core, config }) {
  const owner = context.repo.owner;
  const repo = context.repo.repo;

  const issue = context.payload.issue;
  const comment = context.payload.comment;
  const requester = comment?.user?.login;
  const body = comment?.body || '';

  if (!issue || !comment || !requester) return;
  if (issue.pull_request) return; // Ignore PR comments.
  if (issue.state !== 'open') return;

  const issueNumber = issue.number;
  const issueAuthor = issue.user?.login;
  const currentAssignees = (issue.assignees || []).map((a) => a.login);

  const state = await loadIssueState({ github, owner, repo, issue_number: issueNumber });

  const cmd = parseCommand(body, { assignCmd: config.assignCmd, unassignCmd: config.unassignCmd });

  if (cmd.kind === 'assign') {
    const isBlocked = Boolean(state.blockedReassign?.[requester]);
    const requesterMergedPrCount = await getMergedPrCount({
      github,
      owner,
      repo,
      author: requester,
    });
    const requesterOpenAssignedIssuesCount = await getOpenAssignedIssuesCount({
      github,
      owner,
      repo,
      assignee: requester,
    });

    const decision = decideAssign({
      requester,
      issueAuthor,
      currentAssignees,
      maxAssigneesPerIssue: config.maxAssigneesPerIssue,
      requesterOpenAssignedIssuesCount,
      maxAssignmentsPerUser: config.maxAssignmentsPerUser,
      requesterMergedPrCount,
      bypassMergedPrThreshold: config.bypassMergedPrThreshold,
      isRequesterBlockedFromReassign: isBlocked,
    });

    if (!decision.ok) {
      let msg = 'Cannot assign.';
      if (decision.reason === 'issue_max_assignees') {
        msg = messages.issueMaxAssigneesMessage({
          currentAssignees,
          maxAssigneesPerIssue: config.maxAssigneesPerIssue,
        });
      } else if (decision.reason === 'user_max_assignments') {
        msg = messages.maxAssignmentsMessage({ maxAssignmentsPerUser: config.maxAssignmentsPerUser });
      } else if (decision.reason === 'blocked_reassign') {
        msg = messages.blockedReassignMessage();
      }

      const fullMsg = `@${requester} ${msg}\n\n${messages.rejectedAssignFooter()}`;
      core.setFailed(msg);
      await createIssueComment({
        github,
        owner,
        repo,
        issue_number: issueNumber,
        body: withState(fullMsg, state),
      });
      return;
    }

    const isAlreadyAssigned = currentAssignees
      .map((a) => a.toLowerCase())
      .includes(requester.toLowerCase());
    if (isAlreadyAssigned) {
      const msg = `@${requester} You are already assigned to this issue.`;
      await createIssueComment({
        github,
        owner,
        repo,
        issue_number: issueNumber,
        body: withState(msg, state),
      });
      return;
    }

    await addIssueAssignees({
      github,
      owner,
      repo,
      issue_number: issueNumber,
      assignees: [requester],
    });

    state.assignedAt = state.assignedAt || {};
    state.assignedAt[requester] = nowIso();

    const msg = messages.assignedMessage({ deadlineDays: config.deadlineDays });
    await createIssueComment({
      github,
      owner,
      repo,
      issue_number: issueNumber,
      body: withState(`@${requester} ${msg}`, state),
    });
    return;
  }

  if (cmd.kind === 'unassign') {
    const isAssigned = currentAssignees.map((a) => a.toLowerCase()).includes(requester.toLowerCase());
    if (isAssigned) {
      await removeIssueAssignees({
        github,
        owner,
        repo,
        issue_number: issueNumber,
        assignees: [requester],
      });
    }

    if (state.assignedAt && state.assignedAt[requester]) delete state.assignedAt[requester];

    const msg = isAssigned ? 'Unassigned.' : 'You are not assigned to this issue.';
    await createIssueComment({
      github,
      owner,
      repo,
      issue_number: issueNumber,
      body: withState(`@${requester} ${msg}`, state),
    });
    return;
  }

  if (config.enableSuggestion) {
    if (currentAssignees.length > 0) return;
    if (state.suggested) return;
    if (!shouldSuggestAssign(body)) return;

    state.suggested = true;
    const msg = messages.suggestionMessage({
      assignCmd: config.assignCmd,
      deadlineDays: config.deadlineDays,
    });
    await createIssueComment({
      github,
      owner,
      repo,
      issue_number: issueNumber,
      body: withState(msg, state),
    });
  }
}

module.exports = { handleIssueComment };

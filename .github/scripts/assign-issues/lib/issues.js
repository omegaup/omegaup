const { searchCount, searchAllIssues, listRepoIssues } = require('./github');
const { formatQualifierValue, normalizeLogin } = require('./search');

async function getOpenAssignedIssuesCount({ github, owner, repo, assignee }) {
  const login = normalizeLogin(assignee);
  const q = `repo:${owner}/${repo} is:issue is:open assignee:${formatQualifierValue(login)}`;
  return await searchCount({ github, q });
}

async function listOpenIssuesWithAssignees({ github, owner, repo }) {
  // GitHub Search syntax doesn't reliably support "assignee:*" across all cases.
  // Listing issues and filtering is more robust (and avoids search syntax errors).
  const all = await listRepoIssues({ github, owner, repo, state: 'open' });
  return all.filter((it) => !it.pull_request && Array.isArray(it.assignees) && it.assignees.length > 0);
}

module.exports = { getOpenAssignedIssuesCount, listOpenIssuesWithAssignees };

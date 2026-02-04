const { searchCount, searchAllIssues } = require('./github');

async function getOpenAssignedIssuesCount({ github, owner, repo, assignee }) {
  const q = `repo:${owner}/${repo} is:issue is:open assignee:${assignee}`;
  return await searchCount({ github, q });
}

async function listOpenIssuesWithAssignees({ github, owner, repo }) {
  // Search is the easiest way to fetch only open issues that have at least one assignee.
  const q = `repo:${owner}/${repo} is:issue is:open assignee:*`;
  return await searchAllIssues({ github, q });
}

module.exports = { getOpenAssignedIssuesCount, listOpenIssuesWithAssignees };


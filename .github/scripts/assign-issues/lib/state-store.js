const { defaultState, tryParseStateFromBody } = require('./state');
const { listIssueComments } = require('./github');

async function findIssueState({ github, owner, repo, issue_number }) {
  const comments = await listIssueComments({ github, owner, repo, issue_number, per_page: 100 });
  for (const c of comments) {
    const parsed = tryParseStateFromBody(c.body);
    if (parsed) return parsed;
  }
  return null;
}

async function loadIssueState({ github, owner, repo, issue_number }) {
  return (await findIssueState({ github, owner, repo, issue_number })) || defaultState();
}

module.exports = { loadIssueState, findIssueState };

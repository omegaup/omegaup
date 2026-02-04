async function createIssueComment({ github, owner, repo, issue_number, body }) {
  await github.rest.issues.createComment({ owner, repo, issue_number, body });
}

async function addIssueAssignees({ github, owner, repo, issue_number, assignees }) {
  await github.rest.issues.addAssignees({ owner, repo, issue_number, assignees });
}

async function removeIssueAssignees({ github, owner, repo, issue_number, assignees }) {
  await github.rest.issues.removeAssignees({ owner, repo, issue_number, assignees });
}

async function getIssue({ github, owner, repo, issue_number }) {
  const { data } = await github.rest.issues.get({ owner, repo, issue_number });
  return data;
}

async function listIssueComments({ github, owner, repo, issue_number, per_page = 100 }) {
  const { data } = await github.rest.issues.listComments({
    owner,
    repo,
    issue_number,
    per_page,
    sort: 'created',
    direction: 'desc',
  });
  return data;
}

async function searchCount({ github, q }) {
  const { data } = await github.rest.search.issuesAndPullRequests({ q, per_page: 1 });
  return data.total_count || 0;
}

async function searchAllIssues({ github, q }) {
  return await github.paginate(github.rest.search.issuesAndPullRequests, { q, per_page: 100 });
}

module.exports = {
  createIssueComment,
  addIssueAssignees,
  removeIssueAssignees,
  getIssue,
  listIssueComments,
  searchCount,
  searchAllIssues,
};


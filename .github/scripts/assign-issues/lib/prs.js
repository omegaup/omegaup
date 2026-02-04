const { searchCount } = require('./github');

async function getMergedPrCount({ github, owner, repo, author }) {
  const q = `repo:${owner}/${repo} is:pr is:merged author:${author}`;
  return await searchCount({ github, q });
}

async function hasLinkedPrByAuthorSince({ github, owner, repo, issueNumber, author, sinceIso }) {
  // Use GraphQL to find PR cross-references from the issue timeline.
  // We consider a PR "raised" if the PR author matches and PR.createdAt >= sinceIso.
  const since = new Date(sinceIso);
  if (Number.isNaN(since.getTime())) return false;

  const query = `
    query($owner: String!, $repo: String!, $number: Int!, $cursor: String) {
      repository(owner: $owner, name: $repo) {
        issue(number: $number) {
          timelineItems(itemTypes: [CROSS_REFERENCED_EVENT], first: 100, after: $cursor) {
            nodes {
              __typename
              ... on CrossReferencedEvent {
                source {
                  __typename
                  ... on PullRequest {
                    createdAt
                    author { login }
                  }
                }
              }
            }
            pageInfo { hasNextPage endCursor }
          }
        }
      }
    }
  `;

  let cursor = null;
  const authorLower = String(author).toLowerCase();

  for (;;) {
    const res = await github.graphql(query, {
      owner,
      repo,
      number: issueNumber,
      cursor,
    });

    const items = res?.repository?.issue?.timelineItems;
    const nodes = items?.nodes || [];

    for (const n of nodes) {
      const pr = n?.source;
      if (!pr || pr.__typename !== 'PullRequest') continue;
      const prAuthor = pr?.author?.login;
      if (!prAuthor || String(prAuthor).toLowerCase() !== authorLower) continue;
      const createdAt = new Date(pr.createdAt);
      if (!Number.isNaN(createdAt.getTime()) && createdAt >= since) return true;
    }

    if (!items?.pageInfo?.hasNextPage) break;
    cursor = items.pageInfo.endCursor;
  }

  return false;
}

module.exports = { getMergedPrCount, hasLinkedPrByAuthorSince };


const { getConfig } = require('./lib/config');
const { handleIssueComment } = require('./lib/handlers/issue-comment');
const { handleSweep } = require('./lib/handlers/sweep');

module.exports = async ({ github, context, core }) => {
  const config = getConfig();

  if (context.eventName === 'issue_comment') {
    await handleIssueComment({ github, context, core, config });
    return;
  }

  if (context.eventName === 'schedule' || context.eventName === 'workflow_dispatch') {
    await handleSweep({ github, context, core, config });
    return;
  }

  core.info(`Unsupported event: ${context.eventName}`);
};


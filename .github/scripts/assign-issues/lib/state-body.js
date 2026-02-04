const { formatStateComment } = require('./state');

function withState(visibleBody, state) {
  return `${visibleBody}\n\n${formatStateComment(state)}`;
}

module.exports = { withState };


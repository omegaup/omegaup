(function () {
  if (
    typeof omegaup === 'undefined' ||
    typeof omegaup.API === 'undefined' ||
    typeof omegaup.OmegaUp === 'undefined' ||
    typeof omegaup.UI === 'undefined'
  ) {
    // This should only be visible in development Virtual Machines.
    var statusElement = document.querySelector('#status');

    var statusMessageElement = statusElement.querySelector('.message');
    statusMessageElement.appendChild(document.createTextNode('Please run: '));

    var ttNode = document.createElement('tt');
    ttNode.appendChild(
      document.createTextNode(
        'cd /opt/omegaup && yarn install && yarn run dev-all',
      ),
    );
    statusMessageElement.appendChild(ttNode);

    statusElement.className = 'alert alert-danger';
    statusElement.style.display = 'block';
  }
})();

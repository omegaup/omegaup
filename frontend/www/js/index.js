(function() {
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
        'cd /opt/omegaup && yarn install && yarn run dev',
      ),
    );
    statusMessageElement.appendChild(ttNode);

    statusElement.className = 'alert alert-danger';
    statusElement.style.display = 'block';
  }

  omegaup.OmegaUp.on('ready', function() {
    omegaup.API.Contest.list({ active: 'ACTIVE' })
      .then(function(data) {
        var list = data.results;
        var now = new Date();

        var nextContestsList = document.getElementById('next-contests-list');
        for (var i = 0, len = list.length; i < len && i < 10; i++) {
          var link = document.createElement('a');
          link.href = '/arena/' + omegaup.UI.escape(list[i].alias) + '/';
          link.className = 'list-group-item';
          link.appendChild(document.createTextNode(list[i].title));
          nextContestsList.appendChild(link);
        }
      })
      .fail(omegaup.UI.apiError);
  });
})();

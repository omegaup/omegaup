$('a[data-toggle="tab"]')
    .on('shown.bs.tab', function(e) {
      var target = $(e.target).attr('href');

      // If add-students tab is on focus
      if (target === '#add-students') {
        var courseAlias =
            /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

        // Mount users typeahead
        $('#member-username')
            .typeahead(
                {
                  minLength: 2,
                  highlight: true,
                },
                {
                  source: omegaup.UI.typeaheadWrapper(omegaup.API.searchUsers),
                  displayKey: 'label',
                })
            .on('typeahead:selected', function(item, val, text) {
              $('#member-username').val(val.label);
            });
      }
    });

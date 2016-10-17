$('document')
    .ready(function() {
      $('#problem-search-box')
          .typeahead(
              {
                minLength: 3,
                highlight: true,
              },
              {
                source: omegaup.UI.typeaheadWrapper(function(query, cb) {
                  omegaup.API.searchProblems(
                      query, function(data) { cb(data.results); });
                }),
                displayKey: 'title',
                templates: {
                  suggestion: function(elm) {
                    return '<strong>' + elm.title + '</strong> (' + elm.alias +
                           ')';
                  }
                }
              })
          .on('typeahead:selected', function(item, val, text) {
            $('#problem-search-box').val(val.title);
          });
    });

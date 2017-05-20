(function() {
  function fillContestsTable() {
    var deferred = $('#show-admin-contests').prop('checked') ?
                       omegaup.API.Contest.adminList() :
                       omegaup.API.Contest.myList();
    deferred
        .then(function(result) {
          contestList.contests = result.contests;
        })
        .fail(omegaup.UI.apiError);
  }
  fillContestsTable();

  $('#show-admin-contests').click(fillContestsTable);

  function makePublic(isPublic) {
    return function() {
      omegaup.UI.bulkOperation(
          function(alias, resolve, reject) {
            omegaup.API.Contest
                .update({contest_alias: alias, 'public': isPublic ? 1 : 0})
                .then(resolve)
                .fail(reject);
          },
          function() { fillContestsTable(); });
    };
  }

  $('#bulk-make-public').click(makePublic(true));
  $('#bulk-make-private').click(makePublic(false));
})();

omegaup.OmegaUp.on('ready', function() {
  // TODO(pablo): fix dependency. refreshStudentList is not defined.
  refreshStudentList();
  // TODO(pablo): fix dependency. koStudentsList is not global.
  ko.applyBindings(koStudentsList);
});

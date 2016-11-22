$('document')
    .ready(function() {
      refreshStudentList();
      ko.applyBindings(koStudentsList);
    });

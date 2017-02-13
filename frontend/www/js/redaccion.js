omegaup.OmegaUp.on('ready', function() {
  var converter1 = Markdown.getSanitizingConverter();
  var editor1 = new Markdown.Editor(converter1);
  editor1.hooks.chain('onPreviewRefresh', function() {
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, $('#wmd-preview').get(0)]);
  });
  editor1.run();

  // Ask the user if they want to restore the last draft
  if (localStorage.getItem('wmdinput')) {
    var r = confirm('¿Deseas restaurar tu última redacción?');
    if (r == true) {
      $('#wmd-input').val(localStorage.getItem('wmdinput'));
    }
  }

  $('#wmd-input').keyup(function() {
    localStorage.setItem('wmdinput', $('#wmd-input').val());
  });
});

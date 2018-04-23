omegaup.OmegaUp.on('ready', function() {
  var markdownConverter = omegaup.UI.markdownConverter({preview: true});
  var markdownEditor = new Markdown.Editor(markdownConverter);
  markdownEditor.hooks.chain('onPreviewRefresh', function() {
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, $('#wmd-preview').get(0)]);
  });
  markdownEditor.run();

  // Ask the user if they want to restore the last draft
  if (localStorage.getItem('wmdinput')) {
    var r = confirm('¿Deseas restaurar tu última redacción?');
    if (r == true) {
      $('#wmd-input').val(localStorage.getItem('wmdinput'));
    }
  }

  $('#wmd-input')
      .on('keyup', function() {
        localStorage.setItem('wmdinput', $('#wmd-input').val());
      });
});

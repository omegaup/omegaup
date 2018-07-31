(function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);
  var markdownConverter = omegaup.UI.markdownConverter({preview: true});

  var statement = document.querySelector('div.statement');
  statement.innerHTML = markdownConverter.makeHtmlWithImages(
      payload.statement.markdown, payload.statement.images);
  MathJax.Hub.Queue(['Typeset', MathJax.Hub, statement]);
})();

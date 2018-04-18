(function() {
  var payload = JSON.parse(document.getElementById('payload').innerText);
  var converter = Markdown.getSanitizingConverter();

  var statement = document.querySelector('div.statement');
  statement.innerHTML = converter.makeHtml(payload.problem_statement);
  MathJax.Hub.Queue(['Typeset', MathJax.Hub, statement]);
})();

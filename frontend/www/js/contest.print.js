(function() {
  var markdownConverter = omegaup.UI.markdownConverter({ preview: true });

  var problems = document.querySelectorAll('div.problem');
  for (var i = 0; i < problems.length; i++) {
    var problem = problems[i];
    var output = problem.querySelector('div.statement');
    var payload = JSON.parse(problem.querySelector('script.payload').innerText);

    output.innerHTML = markdownConverter.makeHtmlWithImages(
      payload.statement.markdown,
      payload.statement.images,
      payload.settings,
    );
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, output]);
  }
})();

omegaup.OmegaUp.on('ready', function() {
  var markdownConverter = omegaup.UI.markdownConverter({preview: true});
  var markdownEditor = new Markdown.Editor(markdownConverter);
  var originalRedaction = `# Descripción

Esta es la descripción del problema. Inventa una historia creativa.
Puedes utilizar matemáticas inline para hacer $x_i, y_i$, o $z_i$ o incluso:
$$x=\\frac{b\\pm \\sqrt{b^2 -4ac}}{2a}$$

# Entrada

Aquí va la descripción de la entrada del problema.

# Salida

Esta es la descripción de la salida esperada.

# Ejemplo

||input
1
2
||output
Case #1: 3
||description
Explicación
||input
5
10
||output
Case #2: 15
||end

# Límites

* Aquí
* Van
* Los
* Límites`;

  markdownEditor.hooks.chain('onPreviewRefresh', function() {
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, $('#wmd-preview').get(0)]);
  });
  markdownEditor.run();

  // Ask the user if they want to restore the last draft
  if (localStorage.getItem('wmdinput')) {
    $('#wmd-input').val(localStorage.getItem('wmdinput'));
  } else {
    $('#wmd-input').val(originalRedaction);
  }

  $('#restart-problem')
      .on('click', function(evt) {
        $('#wmd-input').val(originalRedaction);
        markdownEditor.refreshPreview();
        localStorage.setItem('wmdinput', $('#wmd-input').val());
      });

  $('#wmd-input')
      .on('keyup', function() {
        localStorage.setItem('wmdinput', $('#wmd-input').val());
      });
});

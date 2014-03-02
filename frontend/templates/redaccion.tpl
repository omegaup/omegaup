{assign var="htmlTitle" value="{#omegaupTitleRedaccion#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
<script type="text/javascript" src="/js/pagedown/Markdown.Converter.js"></script>
<script type="text/javascript" src="/js/pagedown/Markdown.Sanitizer.js"></script>
<script type="text/javascript" src="/js/pagedown/Markdown.Editor.js"></script>
<link rel="stylesheet" type="text/css" href="/js/pagedown/demo/browser/demo.css" />
<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>

<div class="post">
<div id="wmd-preview" class="problem-statement"></div>
<div id="wmd-panel">
	<div id="wmd-button-bar"></div>
	<textarea class="wmd-input" id="wmd-input"># Descripción

Esta es la descripción del problema. Inventa una historia creativa. Puedes utilizar matemáticas inline para hacer $x_i, y_i$, o $z_i$ o incluso: $$x=\frac{ldelim}b\pm \sqrt{ldelim}b^2 -4ac{rdelim}{rdelim}{ldelim}2a{rdelim}$$

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
* Límites</textarea>
</div>
<script type="text/javascript">
		(function () {ldelim}
				var converter1 = Markdown.getSanitizingConverter();
				var editor1 = new Markdown.Editor(converter1);
				editor1.hooks.chain("onPreviewRefresh", function() {ldelim}
					MathJax.Hub.Queue(["Typeset", MathJax.Hub, $('#wmd-preview').get(0)]);
				{rdelim});
				editor1.run();
		{rdelim})();
</script>
<div style='clear: both;'></div>
</div>
{include file='footer.tpl'}

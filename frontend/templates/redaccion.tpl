{include file='head.tpl' htmlTitle="{#omegaupTitleRedaccion#}" inline}
<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js"}" defer></script>
<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Sanitizer.js"}" defer></script>
<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Editor.js"}" defer></script>
<link rel="stylesheet" type="text/css" href="/css/markdown-editor-widgets.css" />

<div class="post">
<div id="problem">
<div id="wmd-preview-wrapper">
<h1 style="text-align: center;">Nombre del problema</h1>
<div id="wmd-preview" class="statement"></div>
<hr/>
<div><em>Fuente: fuente</em></div>
<div><em>Subido por: tu_usuario</em></div>
</div>
<div id="wmd-panel">
	<div id="wmd-button-bar"></div><button id="reset-statement">Restaurar</button>
	<textarea class="wmd-input" id="wmd-input"></textarea>
</div>
<script type="text/javascript" src="{version_hash src="/js/redaccion.js"}" defer></script>
<div style='clear: both;'></div>
</div>
</div>
{include file='footer.tpl' inline}

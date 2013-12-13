{include file='head.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleRedaccion#}"}
{include file='mainmenu.tpl'}
<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>
<div style="width: 920px; position: relative; margin: 0 auto 0 auto; " class="post">
	<form method='POST' action='redaccion.php' style="padding: 2em;">
		<textarea id='markdownSource' name='source'>{$source}</textarea>
		<div id='markdownPreview' class='problem-statement'>{$markdown}</div>
		<div style='clear: both;'><input value='Previsualización' type='submit'/></div>
	</form>
</div>
{include file='footer.tpl'}

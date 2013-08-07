{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}

<!--
<div class="post">
	<div class="copy " >
		concursos lista opciones,
		nuevo conruso
	</div>
</div>
-->
<div class="post">
	<div class="copy">		
		<div class="POS Boton" id="contest-create">Crear un concurso</div>
		<div class="POS Boton" id="scoreboard-merge">Unir scoreboards</div>
	</div>
</div>

<div id="parent_contest_list">
	{include file='contest.list.tpl'}
</div>

<script>
	$('#contest-create').click(function() {
		window.location.assign("/contestcreate.php");
	});
	
	$('#scoreboard-merge').click(function() {
		window.location.assign("/scoreboardmerge.php");
	});
</script>
	
{include file='footer.tpl'}

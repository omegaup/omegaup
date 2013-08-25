{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}

<div class="post">
	<div class="bottom-margin">
		<a href="/contestcreate.php" class="btn btn-primary" id="contest-create">Crear un concurso</a>
		<a href="/scoreboardmerge.php" class="btn btn-default" id="scoreboard-merge">Unir scoreboards</a>
	</div>

	<div id="parent_contest_list">
		{include file='contest.list.tpl'}
	</div>
</div>

<script>
	$(".navbar #nav-contests").addClass("active");
</script>
	
{include file='footer.tpl'}

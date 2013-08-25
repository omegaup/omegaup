{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}

<div class="post">
	<a href="/contestcreate.php" class="btn btn-primary" id="contest-create">Crear un concurso</a>
	<a href="/scoreboardmerge.php" class="btn btn-default" id="scoreboard-merge">Unir scoreboards</a>

	<div id="parent_contest_list">
		{include file='contest.list.tpl'}
	</div>
</div>
	
{include file='footer.tpl'}

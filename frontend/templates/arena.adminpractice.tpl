{include file='head.tpl' jsfile={version_hash src='/ux/admin.js'} inArena=true inline}
			<div id="title">
				<h1 class="contest-title">Envíos globales</h1>
			</div>
			<div id="runs">
{include file='arena.runs.tpl' show_pager=true show_user=true show_problem=true show_rejudge=true show_details=true show_contest=true inline}
			</div>
		</div>
		<div id="overlay">
			<div id="run-details"></div>
		</div>
{include file='common.analytics.tpl' inline}
	</body>
</html>

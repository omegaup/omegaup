{include file='head.tpl' jsfile={version_hash src='/ux/admin.js'} inArena=true}
			<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/admin_arena.js"}"></script>
			<div id="title">
				<h1 class="contest-title">Envíos globales</h1>
			</div>
			<div id="runs">
{include file='arena.runs.tpl' show_pager=true show_user=true show_problem=true show_rejudge=true show_details=true show_contest=true}
			</div>
		</div>
		<div id="overlay">
{include file='arena.rundetails.tpl'}
		</div>
	</body>
</html>

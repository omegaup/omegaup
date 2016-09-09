{include file='arena.head.tpl' jsfile='/ux/admin.js?ver=c9161a'}
			<script type="text/javascript" src="/ux/libadmin.js?ver=0f3950"></script>
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

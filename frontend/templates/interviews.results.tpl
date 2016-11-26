{include file='head.tpl' htmlTitle='{#interviewList#}'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span><small></small></h1>
	<h3><small></small></h3>
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">{#omegaupTitleProfile#}</h3>
	</div>

{include file='profile.basicinfo.tpl'}

	<div class="panel-body">
	</div>
</div>

<div class="panel panel-primary">
{include file='arena.runs.tpl' show_pager=true show_points=true show_user=true show_problem=true show_rejudge=true show_details=true}
</div>

<script type="text/javascript" src="{version_hash src="/js/third_party/jquery.ba-hashchange.js"}"></script>

<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/admin_arena.js"}"></script>
<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/arena.js"}"></script>

<script type="text/javascript" src="{version_hash src="/js/interviews.results.js"}"></script>
{include file='footer.tpl'}


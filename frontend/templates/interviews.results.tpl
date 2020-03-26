{include file='head.tpl' htmlTitle="{#interviewList#}" inline}

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span><small></small></h1>
	<h3><small></small></h3>
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">{#omegaupTitleProfile#}</h3>
	</div>

{include file='profile.basicinfo.tpl' inline}

	<div class="panel-body">
	</div>
</div>

<div class="panel panel-primary">
{include file='arena.runs.tpl' show_pager=true show_points=true show_user=true show_problem=true show_rejudge=true show_details=true inline}
</div>

<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/arena.js"}" defer></script>
<script type="text/javascript" src="{version_hash src="/js/interviews.results.js"}" defer></script>
{include file='footer.tpl' inline}


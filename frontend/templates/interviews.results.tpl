{assign var="htmlTitle" value="{#interviewList#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="page-header">
	<h1><span>{#frontPageLoading#}</span> <small></small></h1>
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

<script type="text/javascript" src="/js/jquery.ba-hashchange.js?ver=8c26ca"></script>
<script type="text/javascript" src="/js/knockout-4.3.0.js?ver=059d58"></script>
<script type="text/javascript" src="/js/knockout-secure-binding.min.js?ver=81a2a3"></script>

<script type="text/javascript" src="/ux/libadmin.js?ver=16702b"></script>
<script type="text/javascript" src="/ux/libarena.js?ver=ea2329"></script>
<script type="text/javascript" src="/ux/admin.js?ver=7a6498"></script>

<script type="text/javascript" src="/js/interviews.results.js?ver=758257"></script>
{include file='footer.tpl'}


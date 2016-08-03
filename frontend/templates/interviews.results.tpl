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

<script type="text/javascript" src="/ux/libadmin.js?ver=4ef011"></script>
<script type="text/javascript" src="/ux/libarena.js?ver=b48fbf"></script>
<script type="text/javascript" src="/ux/admin.js?ver=d65a4c"></script>

<script type="text/javascript" src="/js/interviews.results.js?ver=b7b5b4"></script>
{include file='footer.tpl'}


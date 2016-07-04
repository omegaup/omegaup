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

<script type="text/javascript" src="/js/interviews.results.js?ver=cecdb8"></script>
{include file='footer.tpl'}


{assign var="htmlTitle" value="{#omegaupTitleProblems#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div id="parent_problems_list">
	{include file='problem.list.tpl'}
</div>

<script>
	$(".navbar #nav-problems").addClass("active");
</script>

{include file='footer.tpl'}

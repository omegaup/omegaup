{include file='head.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleProblems#}"}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div id="parent_problems_list">
	{include file='problems.list.tpl'}
</div>

<script>
	$(".navbar #nav-problems").addClass("active");
</script>

{include file='footer.tpl'}

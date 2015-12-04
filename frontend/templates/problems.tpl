{assign var="htmlTitle" value="{#omegaupTitleProblems#}"}
{include file='head.tpl' navbarSection='problems'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div id="parent_problems_list">
	{include file='problem.list.tpl'}
</div>

{include file='footer.tpl'}

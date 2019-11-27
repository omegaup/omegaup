{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' htmlTitle="{#omegaupTitleProblemStats#}" inline}

<div id="problem-stats"></div>

{if isset($smarty.get.problem)}
{js_include entrypoint="problem_stats"}
{/if}

{include file='footer.tpl' inline}

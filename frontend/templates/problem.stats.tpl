{include file='redirect.tpl'}
{include file='head.tpl' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblemStats#}"}

<div id="problem-stats"></div>

{if isset($smarty.get.problem)}
<script type="text/javascript" src="{version_hash src="/js/dist/problem_stats.js"}"></script>
{/if}

{include file='footer.tpl'}

{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle='{#omegaupTitleProblemStats#}'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<h1>Estadísticas en vivo</h1>
		<h2><div id="total-runs"></div> </h2>
		<div id="verdict-chart"></div>
		<div id="cases-distribution-chart"></div>
		<div id="pending-runs-chart"></div>
	</div>
</div>

{if isset($smarty.get.problem)}
<script type="text/javascript" src="{version_hash src="/js/problem.stats.js"}"></script>
{/if}

{include file='footer.tpl'}

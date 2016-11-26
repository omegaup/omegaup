{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle="{#omegaupTitleContestStats#}"}

<div class="post">
	<div class="copy">
		<h1>Estadísticas en vivo</h1>
		<h2><div id="total-runs"></div> </h2>
		<div id="verdict-chart"></div>
		<div id="distribution-chart"></div>
		<div id="pending-runs-chart"></div>
	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/contest.stats.js"}"></script>

{include file='footer.tpl'}

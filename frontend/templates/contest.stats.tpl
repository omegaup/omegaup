{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContestStats#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<h1>Estadísticas en vivo</h1>
		<h2><div id="total-runs"></div> </h2>
		<div id="verdict-chart"></div>
		<div id="distribution-chart"></div>
		<div id="pending-runs-chart"></div>
	</div>
</div>

<script type="text/javascript" src="/js/contest.stats.js?ver=ec0e2c"></script>

{include file='footer.tpl'}

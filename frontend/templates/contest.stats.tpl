{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<h1>Estadísticas en vivo</h1>				
		<h2><div id="total-runs"></div> </h2>
		<div id="veredict-chart"></div>
		<div id="distribution-chart"></div>
		<div id="pending-runs-chart"></div>
	</div>
</div>

<script>
		
	
	{IF isset($smarty.get.contest)}
	
	Highcharts.setOptions({
            global: {
                useUTC: false
            }
	});
	
	var stats = null;
	var callStatsApiTimeout = 10 * 1000;
	var updateRunCountsChartTimeout = callStatsApiTimeout;
	var updatePendingRunsChartTimeout = callStatsApiTimeout / 2;
	
	
	function getStats() {
		omegaup.getContestStats('{$smarty.get.contest}', function (s) { if( s.status == "ok" ) { stats = s; drawCharts(); } });
		updateStats();
	}
	
	function updateStats() {
		setTimeout( function() { getStats(); }, callStatsApiTimeout);
	}
	
	
	function updateRunCountsData() {		
		window.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(stats));
		window.distribution_chart.series[0].setData(oGraph.getDistribution(stats));
		setTimeout(updateRunCountsData, updateRunCountsChartTimeout);
	}
	
	function drawCharts() {	

		$('#total-runs').html('Total de envíos: ' + stats.total_runs);	
	
		// This function is called after we call getStats multiple times. We just need to draw once.
		if (window.run_counts_chart != null) {
			return;
		}

		// Draw veredict counts pie chart
		window.run_counts_chart = oGraph.veredictCounts('veredict-chart', '{$smarty.get.contest}', stats);
				
		// Draw distribution of scores chart
		window.distribution_chart = oGraph.distributionChart('distribution-chart', '{$smarty.get.contest}', stats);
	}		
			
	getStats();
	
	setTimeout(updateRunCountsData, updateRunCountsChartTimeout);
		
	// Pending runs chart	
    window.pending_chart = oGraph.pendingRuns(updatePendingRunsChartTimeout, function() { return stats.pending_runs.length; });

	
	{/IF}
</script>

{include file='footer.tpl'}

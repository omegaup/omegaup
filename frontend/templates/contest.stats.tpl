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
	
	
	function getDistribution() {
		var distribution = [];
		for (var val in stats.distribution) {
			distribution.push(parseInt(stats.distribution[val]));
		}
		
		return distribution;
	}
	
	function updateRunCountsData() {		
		window.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(stats));
		window.distribution_chart.series[0].setData(getDistribution());
		setTimeout(updateRunCountsData, updateRunCountsChartTimeout);
	}
	
	function drawCharts() {	

		$('#total-runs').html('Total de envíos: ' + stats.total_runs);	
	
		if (window.run_counts_chart != null) {
			return;
		}

		// Draw veredict counts pie chart
		window.run_counts_chart = oGraph.veredictCounts('veredict-chart', '{$smarty.get.contest}', stats);
				
		var categories_vals = [];
		var separator = 0;		
		for (var val in stats.distribution) {
		
			categories_vals[val] = separator;
				
			separator += stats.size_of_bucket;			
		}
		
		
		window.distribution_chart = new Highcharts.Chart ({
			chart: {
                type: 'column',
				renderTo: 'distribution-chart'
            },
            title: {
                text: 'Distribución de puntajes del concurso {$smarty.get.contest}'
            },            
            xAxis: {
               categories: categories_vals,
				title: {
					text: 'Distribución de puntos en 100 intervalos'
				},
				labels: {
					formatter: function() {
						if (this.value % 10 == 0) {
							return this.value;

						}
						else {
							return '';
						}
					}
				}
            },
            yAxis: {
                min: 0,
                title: {
                    text: '# Concursantes'
                }
            },
            tooltip: {
				
            },
            plotOptions: {
                column: {
                    pointPadding: 0.2,
                    borderWidth: 0
                }
            },
            series: [{
                name: 'Número de concursantes',
                data: getDistribution()
    
            }]
        });
	}		
			
	getStats();
	
	setTimeout(updateRunCountsData, updateRunCountsChartTimeout);
		
	// Pending runs chart	
    window.pending_chart = oGraph.pendingRuns(updatePendingRunsChartTimeout, function() { return stats.pending_runs.length; });

	
	{/IF}
</script>

{include file='footer.tpl'}

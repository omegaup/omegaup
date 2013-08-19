{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<h1>Estadísticas en vivo</h1>				
		<h2><div id="total-runs"></div> </h2>
		<div id="veredict-chart"></div>
		<div id="cases-distribution-chart"></div>
		<div id="pending-runs-chart"></div>
	</div>
</div>

<script>
			
	
	{IF isset($smarty.get.problem)}
	
	Highcharts.setOptions({
            global: {
                useUTC: false
            }
	});
	
	var stats = null;
	var callStatsApiTimeout = 10 * 1000;
	var updateRunCountsChart = callStatsApiTimeout;
	var updatePendingRunsChart = callStatsApiTimeout / 2;
	
	
	function getStats() {
		omegaup.getProblemStats('{$smarty.get.problem}', function (s) { if( s.status == "ok" ) { stats = s; drawCharts(); } });
		updateStats();
	}
	
	function updateStats() {
		setTimeout( function() { getStats(); }, callStatsApiTimeout);
	}		
		
	function getCasesDistribution() {
		var casesCounts = [];
		for (var k in stats.cases_stats) {			
			casesCounts.push(parseInt(stats.cases_stats[k]));
		}
		
		return casesCounts;
	}
	
	
	function updateRunCountsData() {		
		window.run_counts_chart.series[0].setData(oGraph.normalizeRunCounts(stats));
		window.cases_distribution_chart.series[0].setData(getCasesDistribution());
		setTimeout(updateRunCountsData, updateRunCountsChart);
	}
	
	function drawCharts() {	

		$('#total-runs').html('Total de envíos: ' + stats.total_runs);	
	
		if (window.run_counts_chart != null) {
			return;
		}

		// Draw veredict counts pie chart
		window.run_counts_chart = oGraph.veredictCounts('veredict-chart', '{$smarty.get.problem}', stats);													
					
		var casesNames = [];
		var casesCounts = [];
		for (var k in stats.cases_stats) {
			casesNames.push(k);
			casesCounts.push(parseInt(stats.cases_stats[k]));
		}

		// Cases distribution chart
		window.cases_distribution_chart = new Highcharts.Chart({
            chart: {
                type: 'column',
				renderTo: 'cases-distribution-chart'
            },
            title: {
                text: 'Soluciones correctas caso por caso'
            },            
            xAxis: {
                categories: casesNames
            },
            yAxis: {
                min: 0,
                title: {
                    text: 'Número de soluciones'
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
                name: 'Número de soluciones',
                data: casesCounts
    
            }]
		});
	}
			
			
	getStats();
	
	setTimeout(updateRunCountsData, updateRunCountsChart);	
	
	// Pending runs chart	
    window.pending_chart = new Highcharts.Chart ({
		chart: {
			type: 'spline',
			animation: Highcharts.svg, // don't animate in old IE
			marginRight: 10,
			renderTo: 'pending-runs-chart',
			events: {
				load: function() {

					// set up the updating of the chart each second
					var series = this.series[0];
					setInterval(function() {
						var x = (new Date()).getTime(), // current time
							y = stats.pending_runs.length;
						series.addPoint([x, y], true, true);
					}, updatePendingRunsChart);
				}
			}
		},
		title: {
			text: 'Envíos aun no revisados'
		},
		xAxis: {
			type: 'datetime',
			tickPixelInterval: 200
		},
		yAxis: {
			title: {
				text: 'Total'
			},
			plotLines: [{
				value: 0,
				width: 1,
				color: '#808080'
			}]
		},
		tooltip: {
			formatter: function() {
					return '<b>'+ this.series.name +'</b><br/>'+
					Highcharts.dateFormat('%Y-%m-%d %H:%M:%S', this.x) +'<br/>'+
					Highcharts.numberFormat(this.y, 2);
			}
		},
		legend: {
			enabled: false
		},
		exporting: {
			enabled: false
		},
		series: [{
			name: 'Runs pendientes',
			data: (function() {
				// generate an array of random data
				var data = [],
					time = (new Date()).getTime(),
					i;

				for (i = -5; i <= 0; i++) {
					data.push({
						x: time + i * 1000,
						y: 0
					});
				}
				return data;
			})()
		}]
	});

	
	{/IF}
</script>

{include file='footer.tpl'}
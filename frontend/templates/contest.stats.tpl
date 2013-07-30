{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="post">
	<div class="copy">
		<h1>Estadísticas en vivo</h1>				
		<div id="veredict-chart"></div>
		<div id="pending-runs-chart" style="width: 560px; height: 250px; margin-left: auto ; margin-right: auto ;"></div>
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
	var updateRunCountsChart = callStatsApiTimeout;
	var updatePendingRunsChart = callStatsApiTimeout;
	
	
	function getStats() {
		omegaup.getContestStats('{$smarty.get.contest}', function (s) { stats = s; drawCharts(); });
		updateStats();
	}
	
	function updateStats() {
		setTimeout( function() { getStats(); }, callStatsApiTimeout);
	}
	
	function getRunCountsNormalizedData() {
		return [
				['WA',   (stats.veredict_counts["WA"] / stats.total_runs) * 100],
				['PA',   (stats.veredict_counts["PA"] / stats.total_runs) * 100],
				{
					name: 'AC',
					y: (stats.veredict_counts["AC"] / stats.total_runs) * 100,
					sliced: true,
					selected: true
				},
				['TLE',   (stats.veredict_counts["TLE"] / stats.total_runs) * 100],
				['MLE',   (stats.veredict_counts["MLE"] / stats.total_runs) * 100],
				['OLE',   (stats.veredict_counts["OLE"] / stats.total_runs) * 100],
				['RTE',   (stats.veredict_counts["RTE"] / stats.total_runs) * 100],
				['CE',   (stats.veredict_counts["CE"] / stats.total_runs) * 100],
				['JE',   (stats.veredict_counts["JE"] / stats.total_runs) * 100],
			];	
	}
	
	function updateRunCountsData() {		
		window.run_counts_chart.series[0].setData(getRunCountsNormalizedData());
		setTimeout(updateRunCountsData, updateRunCountsChart);
	}
	
	function drawCharts() {		
	
		if (window.run_counts_chart != null) {
			return;
		}

		window.run_counts_chart = new Highcharts.Chart ({
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				renderTo: 'veredict-chart'				
			},
			title: {
				text: 'Veredictos en el concurso {$smarty.get.contest}'
			},
			tooltip: {
				formatter: function() {
								return '<b>'+ this.series.name +'</b>: '+ this.percentage.toFixed(2) +' %';
						}

			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						color: '#000000',
						connectorColor: '#000000',					
						formatter: function() {
								return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(2) +' %';
						}
					}
				}
			},
			series: [{
				type: 'pie',
				name: 'Proporción',
				data: getRunCountsNormalizedData()
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
			tickPixelInterval: 150
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

				for (i = -2; i <= 0; i++) {
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
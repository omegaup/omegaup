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
	var updateRunCountsChart = callStatsApiTimeout;
	var updatePendingRunsChart = callStatsApiTimeout / 2;
	
	
	function getStats() {
		omegaup.getContestStats('{$smarty.get.contest}', function (s) { if( s.status == "ok" ) { stats = s; drawCharts(); } });
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
	
	function getDistribution() {
		var distribution = [];
		for (var val in stats.distribution) {
			distribution.push(parseInt(stats.distribution[val]));
		}
		
		return distribution;
	}
	
	function updateRunCountsData() {		
		window.run_counts_chart.series[0].setData(getRunCountsNormalizedData());
		window.distribution_chart.series[0].setData(getDistribution());
		setTimeout(updateRunCountsData, updateRunCountsChart);
	}
	
	function drawCharts() {	

		$('#total-runs').html('Total de envíos: ' + stats.total_runs);	
	
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
								return '<b>Envíos</b>: '+ stats.veredict_counts[this.point.name] ;
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
								return '<b>'+ this.point.name +'</b>: '+ this.percentage.toFixed(2) +' % ('+ stats.veredict_counts[this.point.name] +')' ;
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

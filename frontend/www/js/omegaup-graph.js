
function OmegaupGraph() {
	var self = this;	
}

OmegaupGraph.prototype.veredictCounts = function(renderTo, title, stats) {
	return new Highcharts.Chart ({
		chart: {
			plotBackgroundColor: null,
			plotBorderWidth: null,
			plotShadow: false,
			renderTo: renderTo
		},
		title: {
			text: 'Veredictos de ' + title
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
			data: this.normalizeRunCounts(stats)
		}]
	});
};
	
OmegaupGraph.prototype.normalizeRunCounts = function(stats) {
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
};

OmegaupGraph.prototype.pendingRuns = function(refreshRate, updateStatsFn) {
	return new Highcharts.Chart ({
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
							y = updateStatsFn();
						series.addPoint([x, y], true, true);
					}, refreshRate);
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
};
		


var oGraph = new OmegaupGraph();
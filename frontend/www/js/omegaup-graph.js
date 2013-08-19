
function OmegaupGraph() {
	var self = this;	
}

OmegaupGraph.prototype.runCounts = function(renderTo, currentUserName, stats) {
		return new Highcharts.Chart ({
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				renderTo: renderTo
			},
			title: {
				text: 'Veredictos de ' + currentUserName
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
		


var oGraph = new OmegaupGraph();
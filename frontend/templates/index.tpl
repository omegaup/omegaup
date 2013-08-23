{include file='head.tpl'}
{include file='mainmenu.tpl'}

<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>
<div style="width: 920px; position: relative; margin: 0 auto 0 auto; ">
	<table>
	<tr>
		<td>
			<div class="post footer" style="width: 560px; min-height: 400px;">
				<div class="copy">
					omegaUp es un proyecto web enfocado a elevar el nivel de competitividad de desarrolladores de software en America Latina mediante la resolucion de problemas de algoritmos, con un enfoque competitivo y divertido a la vez.
				</div>
				<div class="copy button" id="contest-create">
					Crea tu propio concurso en omegaUp!
				</div>
				<div id="ranking-chart"></div>
			</div>
		</td>
		<td >
			<div class="post footer" style="width: 330px; min-height: 150px;">
				<div class="copy" >
						<h2>Coder del mes de Julio</h2>
						<br/>
						<div id="coder_of_the_month">
							<div class="rss_element">
								<div class="title"><a href="/profile.php?username=charlyhlms">charlyhlms</a></div>
								<div class="body"><a href="/profile.php?username=charlyhlms"><img src="https://secure.gravatar.com/avatar/shalala?s=92"/></a></div>
								<div class="body"><b>Carlos Galeana</b></div>
								<div class="body">Colegio Carol Baur</div>
								<div class="body">Distrito Federal, México</div>
								<div class="body">12 problemas resueltos en Julio</div>
							</div>
						</div>
				</div>
			</div>
<div class="post footer" style="width: 330px; min-height: 300px;">				
				<div class="copy" >					
					<h2>Últimos blog posts</h2>
					<br/>
					<div id="rss_content">Loading...</div>
				</div>
			</div>
		</td>
	</tr>
	</table>
</div>

<script type="text/javascript">
	google.load("feeds", "1");

	// Our callback function, for when a feed is loaded.
	function feedLoaded(result) {
		if (!result.error) 
		{
			// Grab the container we will put the results into
			var container = document.getElementById("rss_content");
			container.innerHTML = '';

			// Loop through the feeds, putting the titles onto the page.
			// Check out the result object for a list of properties returned in each entry.
			// http://code.google.com/apis/ajaxfeeds/documentation/reference.html#JSON
			for (var i = 0; i < result.feed.entries.length; i++)
			{
				var entry = result.feed.entries[i];
				

				var div = document.createElement("div");
				div.className = "rss_element";

				var title = document.createElement("div");
				title.className = "title";
				var a = document.createElement('a');
				a.href = entry.link;
				a.appendChild(document.createTextNode( entry.title ));
				title.appendChild(a);
				div.appendChild(title);

				var publishedDate = document.createElement("div");
				publishedDate.className = "date";
				publishedDate.appendChild(document.createTextNode( entry.publishedDate ));
				div.appendChild(publishedDate);

				var body = document.createElement("div");
				body.className = "body";
				body.appendChild(document.createTextNode( entry.contentSnippet ));
				div.appendChild(body);

				container.appendChild(div);
			}
		}
	}

	function OnLoad() {
	
	  $('#contest-create').click(function() {
		window.location.assign("contestcreate.php");
	  });
	
	  // Create a feed instance that will grab Digg's feed.
	  var feed = new google.feeds.Feed("http://blog.omegaup.com/rss");

	  // Calling load sends the request off.  It requires a callback function.
	  feed.load(feedLoaded);
	  
	  omegaup.runCounts(createChart);	  	  
	}
	
	function createChart(series) {
	
		if (series.total.length == 0) return;	
		
		var dataInSeries = [];
		var acInSeries = [];
		for(var i in series.total) {
			if (series.total.hasOwnProperty(i)) {
				dataInSeries.push(parseInt(series.total[i]));
			}
			if (series.ac.hasOwnProperty(i)) {
				acInSeries.push(parseInt(series.ac[i]));
			}
		}
	
		var minDate = new Date(Date.now());
		minDate.setDate(minDate.getDate()-(30*6));
		
		var minY = acInSeries[0] - (acInSeries[0] * 0.50);
		window.chart = new Highcharts.Chart({			
			chart: {
				type: 'area',
				renderTo: 'ranking-chart',
				height: 300,
				spacingTop: 20
			},
			title: {
				text: 'Envíos totales evaluados por omegaUp'
			},
			xAxis: {
				type: 'datetime',                
                title: {
                    text: null
                }
			},
			yAxis: {
                title: {
                    text: 'Envíos'
                },
				min: minY
            },
			legend: {
                enabled: false
            },
			plotOptions: {
                area: {                    
                    lineWidth: 1,
                    marker: {
                        enabled: false
                    },
                    shadow: false,
                    states: {
                        hover: {
                            lineWidth: 1
                        }
                    },
                    threshold: null
                }
            },
			series: [{
				type: 'area',
				name: 'Envíos',
				pointInterval: 24 * 3600 * 1000,
                pointStart: minDate.getTime(),
				data: dataInSeries.reverse(),
				fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                        stops: [ 
                            [0, Highcharts.getOptions().colors[0]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                        ]
                    }
				},
				{
				type: 'area',
				name: 'ACs',
				pointInterval: 24 * 3600 * 1000,
                pointStart: minDate.getTime(),
				data: acInSeries.reverse(),
				fillColor: {
                        linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                        stops: [ 
                            [0, Highcharts.getOptions().colors[1]],
                            [1, Highcharts.Color(Highcharts.getOptions().colors[1]).setOpacity(0).get('rgba')]
                        ]
                    }
				}
			]
		});
		
		// set legend colors
		/*var rows = $('#ranking tbody tr.inserted');
		for (var r = 0; r < rows.length; r++) {
			$('.legend', rows[r]).css({
				'background-color': r < rankChartLimit ? colors[r] : 'transparent'
			});
		}*/
	}

	google.setOnLoadCallback(OnLoad);
		
</script>

​
{include file='footer.tpl'}

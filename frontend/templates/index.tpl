{include file='head.tpl'}
{include file='mainmenu.tpl'}

<script src="https://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>
<div class="row">
	<div class="col-md-8">
		<div class="panel panel-default">
			<div class="jumbotron no-bottom-margin">
				<h1>{#frontPageWelcome#}</h1>
				<p>{#frontPageDescription#}</p>
				<div class="text-center">
					<a href="contestcreate.php" class="btn btn-primary btn-lg" id="contest-create">{#frontPageCreateContestButton#}</a>
				</div>
			</div>
		</div>

		<div class="panel panel-default">
			<div id="ranking-chart"></div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading">					
				<h3 class="panel-title">{#frontPageBlogPosts#}</h3>
			</div>
			<div class="panel-body">
				<div id="blog-posts" class="media">{#frontPageLoading#}</div>
			</div>
		</div>
	</div>
	
	<div class="col-md-4">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Coder del mes de Agosto</h3>
			</div>
			<div id="coder_of_the_month" class="panel-body">
				<div class="rss_element">
					<h4 class="text-center" id="coder-of-the-month-username"></h4>
					<div class="text-center" id="coder-of-the-month-img"></a></div>
					<div id="coder-of-the-month-name"></div>
					<div id="coder-of-the-month-school"></div>
					<div id="coder-of-the-month-place"></div>					
				</div>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">{#frontPageMaterial#}</h3>
			</div>
			<div id="coder_of_the_month" class="panel-body">
				<a href="https://omegaup.com/img/libropre3.pdf">Descarga en PDF aquí:
				<img src="https://omegaup.com/img/libroluis.gif" width="75%"/>				
				</a>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">{#frontPageNextContests#}</h3>
			</div>
			<ul class="list-group" id="next-contests-list">			
		    </ul>
		</div>
				
		{include file='rank.table.tpl' rank=$rank}		
		
	</div>
</div>

<script type="text/javascript">
	google.load("feeds", "1");

	// Our callback function, for when a feed is loaded.
	function feedLoaded(result) {
		if (!result.error) {
			// Grab the container we will put the results into
			var container = document.getElementById("blog-posts");
			container.innerHTML = '';

			// Loop through the feeds, putting the titles onto the page.
			// Check out the result object for a list of properties returned in each entry.
			// http://code.google.com/apis/ajaxfeeds/documentation/reference.html#JSON
			for (var i = 0; i < result.feed.entries.length; i++) {
				var entry = result.feed.entries[i];
				
				var div = document.createElement("div");
				div.className = "media-body";

				var title = document.createElement("h4");
				title.className = "media-heading";
				var a = document.createElement('a');
				a.href = entry.link;
				a.appendChild(document.createTextNode( entry.title ));
				title.appendChild(a);
				div.appendChild(title);

				var publishedDate = document.createElement("div");
				publishedDate.className = "date";
				publishedDate.appendChild(document.createTextNode(Date.create(entry.publishedDate).short("es")));
				div.appendChild(publishedDate);

				var body = document.createElement("p");
				body.className = "body";
				body.appendChild(document.createTextNode( entry.contentSnippet ));
				div.appendChild(body);

				container.appendChild(div);
			}
		}
	}

	function OnLoad() {
	  // Create a feed instance that will grab Digg's feed.
	  var feed = new google.feeds.Feed("http://blog.omegaup.com/rss");

	  // Calling load sends the request off.  It requires a callback function.
	  feed.load(feedLoaded);
	  
	  omegaup.runCounts(createChart);
	  
	  omegaup.getContests(function (data) {
		var list = data.results;
		var now = new Date();
		
		for (var i = 0, len = list.length; i < len && i < 10; i++) {
			var start = list[i].start_time;
			var end = list[i].finish_time;
			
			if (end > now) {
				$('#next-contests-list').append('<a href="/arena" class="list-group-item">' + omegaup.escape(list[i].title) + '</a>');
			}
		}
	  
	   });
	   
	   omegaup.getCoderOfTheMonth(function (data){ 
		$('#coder-of-the-month-username').append('<a href="/profile/' + data.userinfo.username + '">' + data.userinfo.username + '</a>');
		$('#coder-of-the-month-img').append('<a href="/profile/' + data.userinfo.username + '"><img src=" ' + data.userinfo.gravatar_92 + '">');
		$('#coder-of-the-month-name').append('<b>' + (data.userinfo.name == null ) ? '' : data.userinfo.name + '</b>');
		$('#coder-of-the-month-school').append((data.userinfo.school == null) ? '' : data.userinfo.school);
		$('#coder-of-the-month-place').append( ((data.userinfo.state == null) ? '' : (data.userinfo.state + ",")) + (data.userinfo.country == null) ? '' : data.userinfo.country);
	   });
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

{include file='footer.tpl'}

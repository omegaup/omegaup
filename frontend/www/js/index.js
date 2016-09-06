(function() {
	google.load("feeds", "1");

	// Our callback function, for when a feed is loaded.
	function feedLoaded(result) {
		if (!result.error) {
			// Grab the container we will put the results into
			var container = document.getElementById("blog-posts");
			container.innerHTML = '';

			var bootstrap_ul = document.createElement("ul");
			bootstrap_ul.className = 'list-group';

			container.appendChild(bootstrap_ul);

			// Loop through the feeds, putting the titles onto the page.
			// Check out the result object for a list of properties returned in each entry.
			// http://code.google.com/apis/ajaxfeeds/documentation/reference.html#JSON
			for (var i = 0; i < result.feed.entries.length; i++) {
				var entry = result.feed.entries[i];

				var bootstrap_li = document.createElement("li");
				bootstrap_li.className = "list-group-item";

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
				publishedDate.appendChild(document.createTextNode("Por: " + entry.author));
				publishedDate.appendChild(document.createTextNode(" - " + Date.create(entry.publishedDate).short("es")));
				div.appendChild(publishedDate);

				var body = document.createElement("p");
				body.className = "body";
				body.innerHTML = "<br>" + entry.content.slice(0,entry.content.indexOf("</p>") - 1) + "<b><a href='" + entry.link + "'>... Ver más > </a></b>";
				div.appendChild(body);

				bootstrap_li.appendChild(div);
				bootstrap_ul.appendChild(bootstrap_li);
			}
		}
	}

	function OnLoad() {
	  // Create a feed instance that will grab the site's feed.
	  var feed = new google.feeds.Feed("http://blog.omegaup.com/rss");

	  // Calling load sends the request off.  It requires a callback function.
	  feed.load(feedLoaded);

	  omegaup.API.runCounts(createChart);

		omegaup.API.getContests(function (data) {
			var list = data.results;
			var now = omegaup.OmegaUp.time();

			for (var i = 0, len = list.length; i < len && i < 10; i++) {
				$('#next-contests-list').append(
							'<a href="/arena/' + omegaup.UI.escape(list[i].alias) +
							'" class="list-group-item">' + omegaup.UI.escape(list[i].title) +
							'</a>');
			}
		}, {'active': 'ACTIVE'});
	}

	function createChart(series) {
		if (series.total.length == 0) return;

		var dataInSeries = [];
		var acInSeries = [];
		for (var i in series.total) {
			if (series.total.hasOwnProperty(i)) {
				dataInSeries.push(parseInt(series.total[i]));
			}
			if (series.ac.hasOwnProperty(i)) {
				acInSeries.push(parseInt(series.ac[i]));
			}
		}

		var minDate = omegaup.OmegaUp.time();
		minDate.setDate(minDate.getDate()-(30*3));

		var minY = dataInSeries[0] - (dataInSeries[0] * 0.50);
		window.chart = new Highcharts.Chart({
			chart: {
				type: 'area',
				renderTo: 'runs-chart',
				height: 300,
				spacingTop: 20
			},
			title: {
				text: 'Envíos totales'
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
			}]
		});
	}

	google.setOnLoadCallback(OnLoad);
})();

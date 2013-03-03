{include file='head.tpl'}
{include file='mainmenu.tpl'}

<script src="http://www.google.com/jsapi?key=AIzaSyA5m1Nc8ws2BbmPRwKu5gFradvD_hgq6G0" type="text/javascript"></script>
<div style="width: 920px; position: relative; margin: 0 auto 0 auto; ">
	<table>
	<tr>
		<td>
			<div class="post footer" style="width: 560px; min-height: 300px;">
				<div class="copy">
				</div>
			</div>
		</td>
		<td >
			<div class="post footer" style="width: 330px; min-height: 300px;">
				<div class="copy" >
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
	  // Create a feed instance that will grab Digg's feed.
	  var feed = new google.feeds.Feed("http://blog.omegaup.com/rss");

	  // Calling load sends the request off.  It requires a callback function.
	  feed.load(feedLoaded);
	}

	google.setOnLoadCallback(OnLoad);
</script>

​
{include file='footer.tpl'}

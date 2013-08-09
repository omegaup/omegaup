{if $LOGGED_IN eq '0'} 
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Omegaup</title>
	<meta http-equiv="REFRESH" content="0;url=/login.php"></HEAD>
		<BODY>
			Redirectioning you.
		</BODY>
	</HTML>
{else}
	{include file='head.tpl'}
	{include file='mainmenu.tpl'}

	<div style="width: 920px; position: relative; margin: 0 auto 0 auto; ">
		<table>
		<tr>
			<td>
				<div class="post footer" style="width: 130px; min-height: 300px;">
					<div class="copy">
						{$CURRENT_USER_GRAVATAR_URL_128}
						<div style="color:black">
							<div>Editar</div>
						</div>
					</div>
					
				</div>
			</td>
			<td >
				<div class="post" style="width: 760px; min-height: 300px;">
					<div class="copy" >

						<h1>{$CURRENT_USER_USERNAME}</h1>
						<div id="SettingsPage_Content">
							<ul class="uiList fbSettingsList _4kg _6-h _4ks ">

								<li class="fbSettingsListItem clearfix uiListItem">
								<!--
									<a class="pvm phs fbSettingsListLink clearfix" >
										<span class="pls fbSettingsListItemLabel"><strong>Name</strong></span>
										<span class="fbSettingsListItemContent fcg"><strong>Alan Gonzalez</strong>
										</span>
									</a>
								-->
								<div class="content">

								</div>
								</li>
								<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>Username</strong></span><span class="fbSettingsListItemContent fcg"> http://www.omegaup.com/<strong>{$CURRENT_USER_USERNAME}</strong></span></a>
								<div class="content">
								</div>
								</li>
								<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix"><span class="pls fbSettingsListItemLabel"><strong>Email</strong></span><span class="fbSettingsListItemContent fcg">Primary: <strong>{$CURRENT_USER_EMAIL}</strong>&nbsp;</span></a>
								<div class="content">
								</div>
								</li>

								<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>Language</strong></span><span class="fbSettingsListItemContent fcg"><strong>Español (MX)</strong></span></a>
								<div class="content">
								</div>
								</li>
							</ul>
						</div>
								
						<h1>Estadísticas</h1>
						<div id="veredict-chart"><img src="/media/wait.gif" /></div>
					</div>

			</div>


				<!--
				<div class="post" style="width: 760px; min-height: 300px;">
					<div class="copy" >

						<h1>{$CURRENT_USER_USERNAME}</h1>
						<div id="SettingsPage_Content">
							<ul class="uiList fbSettingsList _4kg _6-h _4ks ">

								<li class="fbSettingsListItem clearfix uiListItem">
									<a class="pvm phs fbSettingsListLink clearfix" >
										<span class="pls fbSettingsListItemLabel"><strong>Name</strong></span>
										<span class="fbSettingsListItemContent fcg">
											<input type="text" value="Alan">
										</span>
									</a>
								<div class="content">

								</div>
								</li>
								<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" rel="async"><span class="pls fbSettingsListItemLabel"><strong>Username</strong></span><span class="fbSettingsListItemContent fcg"> https://omegaup.com/<strong><input type="text" value="{$CURRENT_USER_USERNAME}"></strong></span></a>
								<div class="content">
								</div>
								</li>
								<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" rel="async"><span class="pls fbSettingsListItemLabel"><strong>Email</strong></span><span class="fbSettingsListItemContent fcg">Primary: <strong><input type="text" value="{$CURRENT_USER_EMAIL}"></strong>&nbsp;</span></a>
								<div class="content">
								</div>
								</li>

								<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix"><span class="pls fbSettingsListItemLabel"><strong>Language</strong></span><span class="fbSettingsListItemContent fcg">

									<select>
										<option>asdf</option>
										<option>asdf</option>
									</select>
								</span></a>
								<div class="content">
								</div>
								</li>
							</ul>
						</div>
					</div>

				</div>
				-->
			</td>
		</tr>
		</table>
	</div>

	<script>
		
		var status = null;
		
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
		
		
		
		omegaup.getUserStats(function(data) {
			stats = data;
			
			window.run_counts_chart = new Highcharts.Chart ({
				chart: {
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false,
					renderTo: 'veredict-chart'				
				},
				title: {
					text: 'Veredictos de {$CURRENT_USER_USERNAME}'
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
		
		});
	
	</script>
								

	{include file='footer.tpl'}
{/if}


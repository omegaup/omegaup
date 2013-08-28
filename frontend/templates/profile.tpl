{if $LOGGED_IN eq '0'} 
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Omegaup</title>
	<meta http-equiv="REFRESH" content="0;url=/login.php"></HEAD>
		<BODY>
		</BODY>
	</HTML>
{else}
	{include file='head.tpl'}
	{include file='mainmenu.tpl'}
	{include file='status.tpl'}

	<div class="row" id="inner-content">
		<div class="col-md-2 no-right-padding" id="userbox">
			<div class="panel panel-default" id="userbox-inner">
				<div class="panel-heading">
					<h2 class="panel-title">{$CURRENT_USER_USERNAME}</h2>
				</div>
				<div class="panel-body">
					<div class="thumbnail bottom-margin">{$CURRENT_USER_GRAVATAR_URL_128}</div>
					<div id="profile-edit"><a href="useredit.php" class="btn btn-default">{#profileEdit#}</a></div>
				</div>
			</div>
		</div>
		
		{block name="content"}
		<div class="col-md-10 no-right-padding">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 class="panel-title">Mi info</h2>
				</div>
				<div class="panel-body">
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
							<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileUsername#}</strong></span><span class="fbSettingsListItemContent fcg">https://omegaup.com/profile/<strong id="username-link">{$CURRENT_USER_USERNAME}</strong></span></a>
							<div class="content">
							</div>
							</li>
							<li id="user-email-wrapper" class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix"><span class="pls fbSettingsListItemLabel"><strong>{#profileEmail#}</strong></span><span class="fbSettingsListItemContent fcg">Primary: <strong id="user-email">{$CURRENT_USER_EMAIL}</strong>&nbsp;</span></a>
							<div class="content">
							</div>
							</li>

							<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileCountry#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-country">MX</strong></span></a>
							<div class="content">
							</div>
							</li>

							<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileState#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-state"></strong></span></a>

							<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileSchool#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-school"></strong></span></a>

							<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileGraduationDate#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-graduation-date"></strong></span></a>
							<div class="content">
							</div>
							</li>
						</ul>
					</div>
				</div>
			</div>
							
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 class="panel-title">Concursos <span class="badge" id="contests-total">0</span></h2>
				</div>				
				<table class="table table-striped" id="contest-results">
					<thead>
						<tr>
							<th>Concurso</th>
							<th>Lugar</th>							
						</tr>						
					</thead>
					<tbody>
						
					</tbody>
				</table>				
				<div id="contest-results-wait"><img src="/media/wait.gif" /></div>
			</div>
							
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 class="panel-title">Problemas resueltos <span class="badge" id="problems-solved-total">0</span></h2>
				</div>				
				<table class="table table-striped" id="problems-solved">
					<thead>
						<tr>
							<th>Título</th>
							<th>Título</th>
							<th>Título</th>							
						</tr>						
					</thead>
					<tbody>
						
					</tbody>
				</table>				
				<div id="problems-solved-wait"><img src="/media/wait.gif" /></div>
			</div>
			
			<div class="panel panel-default no-bottom-margin">
				<div class="panel-heading">
					<h2 class="panel-title">Estadísticas</h2>
				</div>
				<div class="panel-body">
					<div id="veredict-chart"><img src="/media/wait.gif" /></div>
				</div>
			</div>
																				
		</div>
		{/block}
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
	</div>

	<script>
		{IF isset($smarty.get.username)}
		var username = "{$smarty.get.username|replace:"\\":""}";
		{ELSE}
		var username = null;
		{/IF}
		
		omegaup.getUserStats(username, function(data) {		
			window.run_counts_chart = oGraph.veredictCounts('veredict-chart', (username == null) ? "{$CURRENT_USER_USERNAME}" : username, data);	
		});
				
		omegaup.getProfile(username, function(data) {
			$('#userbox-inner .thumbnail').html("<img src=" + data.userinfo.gravatar_92 + "/>");
			$('#userbox-inner h2').html(data.userinfo.username);
			$('#username-link').html(data.userinfo.username);
			if (data.userinfo.username != '{$CURRENT_USER_USERNAME}') {
				$('#user-email-wrapper').hide();
				$('#profile-edit').hide();
			}
			$('#user-country').html(data.userinfo.country == null ? "" : data.userinfo.country);
			$('#user-state').html(data.userinfo.state == null ? "" : data.userinfo.state);
			$('#user-school').html(data.userinfo.school == null ? "" : data.userinfo.school);
			$('#user-graduation-date').html(data.userinfo.graduation_date == null ? "" : onlyDateToString(data.userinfo.graduation_date));
		});
		
		omegaup.getContestStatsForUser(username, function(data){
			$('#contest-results-wait').hide();
			t=0;	
			for (var contest_alias in data["contests"]) {
				
				var now = new Date();
				var end = omegaup.time(data["contests"][contest_alias]["data"]["finish_time"] * 1000);
			
				if (data["contests"][contest_alias]["place"] != null && now > end) {
					var title = data["contests"][contest_alias]["data"]["title"];
					var place = data["contests"][contest_alias]["place"];
					var content = "<tr><td><a href='/arena/" + contest_alias + "'>" + title + "</a></td><td><b>" + place + "</b></td></tr>";  
					$('#contest-results tbody').append(content);
					t++;
				}
			}
			
			$('#contests-total').html(t);
		});
		
		omegaup.getProblemsSolved(username, function(data){
			$('#problems-solved-wait').hide();
			
			for (var i = 0; i < data["problems"].length; i++) {
				var content = "<tr>"; 
				
				for (var j = 0; j < 3 && i < data["problems"].length; j++, i++)
				{
					content += "<td><a href='/arena/problem" + data["problems"][i]["alias"] + "'>" + data["problems"][i]["title"] + "</a></td>";  
				}
				i--;
				
				content += "</tr>";
				
				$('#problems-solved tbody').append(content);
			}
			
			$('#problems-solved-total').html(data["problems"].length);
		});
		
	</script>
	
	{include file='footer.tpl'}
{/if}


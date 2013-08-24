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
	{include file='status.tpl'}

	<div class="row" id="inner-content">
		<div class="col-md-2" id="userbox">
			<div class="post" id="userbox-inner">
				<h2>{$CURRENT_USER_USERNAME}</h2>
				<div class="thumbnail">{$CURRENT_USER_GRAVATAR_URL_128}</div>
				<div><a href="useredit.php" class="btn btn-default">Editar perfil</a></div>
			</div>
		</div>
		<div class="post col-md-10">
			<div>
				{block name="content"}
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
						
				<h2>Estadísticas</h2>
				<div id="veredict-chart"><img src="/media/wait.gif" /></div>
				{/block}
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
	</div>

	<script>
		{IF isset($smarty.get.username)}
		var username = "{$smarty.get.username}";
		{ELSE}
		var username = null
		{/IF}
		
		omegaup.getUserStats(username, function(data) {		
			window.run_counts_chart = oGraph.veredictCounts('veredict-chart', (username == null) ? "{$smarty.get.username}" : username, data);	
		});
				
		omegaup.getProfile(username, function(data) {
			$('#profile-picture').html("<img src=" + data.userinfo.gravatar_92 + "/>");
			$('#username').html(data.userinfo.username);
			$('#username-link').html(data.userinfo.username);
			$('#country').html(data.userinfo.country == null ? "" : data.userinfo.country);
			$('#state').html(data.userinfo.state == null ? "" : data.userinfo.state);
			$('#school').html(data.userinfo.school == null ? "" : data.userinfo.school);
			$('#graduation_date').html(data.userinfo.graduation_date == null ? "" : onlyDateToString(data.userinfo.graduation_date));
		});
	</script>
	
	{include file='footer.tpl'}
{/if}


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

	<div style="width: 920px; position: relative; margin: 0 auto 0 auto; ">
		<table>
		<tr>
			<td>
				<div class="post footer" style="width: 130px; min-height: 300px;">
					<div class="copy">
						{$CURRENT_USER_GRAVATAR_URL_128}
						<div style="color:black">
							<input value='Editar perfil' type='submit' class="OK">
						</div>
					</div>
					
				</div>
			</td>
			<td >
				<div class="post" style="width: 760px; min-height: 300px;">
					<div class="copy" >
						{block name="content"}
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
			</td>
		</tr>
		</table>
	</div>

	<script>
		
		$('input.OK').click(function() {
			window.location.assign("useredit.php");
		});
		
		omegaup.getUserStats(function(data) {
		
			window.run_counts_chart = oGraph.veredictCounts('veredict-chart', '{$CURRENT_USER_USERNAME}', data);	
		});
	
	</script>
								

	{include file='footer.tpl'}
{/if}


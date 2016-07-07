			<div class="panel-body">
				<div id="SettingsPage_Content">
					<ul class="uiList fbSettingsList _4kg _6-h _4ks ">
						<li class="fbSettingsListItem clearfix uiListItem">
						<div class="content">

						</div>
						</li>
						<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileUsername#}</strong></span><span class="fbSettingsListItemContent fcg">https://omegaup.com/profile/<strong id="username-link">{$profile.userinfo.username}</strong>/</span></a>
						<div class="content">
						</div>
						</li>
						<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profile#}</strong></span><span class="fbSettingsListItemContent fcg">{$profile.userinfo.name}</span></a>
						<div class="content">
						</div>
						</li>
						{if isset($profile.userinfo.email)}
						<li id="user-email-wrapper" class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix"><span class="pls fbSettingsListItemLabel"><strong>{#profileEmail#}</strong></span><span class="fbSettingsListItemContent fcg">Primary: <strong id="user-email">{$profile.userinfo.email}</strong>&nbsp;</span></a>
						<div class="content">
						</div>
						</li>
						{/if}
						<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileCountry#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-country">{$profile.userinfo.country}</strong></span></a>
						<div class="content">
						</div>
						</li>

						<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileState#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-state">{$profile.userinfo.state}</strong></span></a>

						<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileSchool#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-school">{$profile.userinfo.school}</strong></span></a>

						<li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" ><span class="pls fbSettingsListItemLabel"><strong>{#profileGraduationDate#}</strong></span><span class="fbSettingsListItemContent fcg"><strong id="user-graduation-date">{$profile.userinfo.graduation_date}</strong></span></a>
						<div class="content">
						</div>
						</li>
					</ul>
				</div>
			</div>

{include file='head.tpl'}

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
            <a class="pvm phs fbSettingsListLink clearfix" href="/settings?tab=account&amp;section=name" ajaxify="/ajax/settings/account/name.php" rel="async">
                <span class="pls fbSettingsListItemLabel"><strong>Name</strong></span>
                <span style="padding-left: 23px;" class="uiIconText fbSettingsListItemEdit">
                    <i class="img sp_3ctdza sx_6609b9" style="top: -2px;"></i>Edit
                </span>
                <span class="fbSettingsListItemContent fcg"><strong>Alan Gonzalez</strong>
                </span>
            </a>
        <div class="content">

        </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" href="/settings?tab=account&amp;section=username" ajaxify="/ajax/settings/account/username.php" rel="async"><span class="pls fbSettingsListItemLabel"><strong>Username</strong></span><span style="padding-left: 23px;" class="uiIconText fbSettingsListItemEdit"><i class="img sp_3ctdza sx_6609b9" style="top: -2px;"></i>Edit</span><span class="fbSettingsListItemContent fcg"> http://www.omegaup.com/<strong>{$CURRENT_USER_USERNAME}</strong></span></a>
        <div class="content">
        </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" href="/settings?tab=account&amp;section=email" ajaxify="/ajax/settings/account/email.php" rel="async"><span class="pls fbSettingsListItemLabel"><strong>Email</strong></span><span style="padding-left: 23px;" class="uiIconText fbSettingsListItemEdit"><i class="img sp_3ctdza sx_6609b9" style="top: -2px;"></i>Edit</span><span class="fbSettingsListItemContent fcg">Primary: <strong>alanboy@alanboy.net</strong>&nbsp;</span></a>
        <div class="content">
        </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" href="/settings?tab=account&amp;section=password" ajaxify="/ajax/settings/account/password.php" rel="async"><span class="pls fbSettingsListItemLabel"><strong>Password</strong></span><span style="padding-left: 23px;" class="uiIconText fbSettingsListItemEdit"><i class="img sp_3ctdza sx_6609b9" style="top: -2px;"></i>Edit</span><span class="fbSettingsListItemContent fcg"><abbr title="Thursday, April 5, 2012 at 12:18am" data-utime="1333610326" class="timestamp">Updated about 7 months ago</abbr>.</span></a>
        <div class="content">
        </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem"><a class="pvm phs fbSettingsListLink clearfix" href="/settings?tab=account&amp;section=language" ajaxify="/ajax/settings/account/language.php" rel="async"><span class="pls fbSettingsListItemLabel"><strong>Language</strong></span><span style="padding-left: 23px;" class="uiIconText fbSettingsListItemEdit"><i class="img sp_3ctdza sx_6609b9" style="top: -2px;"></i>Edit</span><span class="fbSettingsListItemContent fcg"><strong>English (US)</strong></span></a>
        <div class="content">
        </div>
        </li>
    </ul>

</div>



                </div>
            </div>
        </td>
    </tr>
    </table>
</div>


{include file='footer.tpl'}


{include file='head.tpl' navbarSection='users' htmlTitle="{#omegaupTitleProfile#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="payload">{['profile' => $profile.userinfo, 'logged_in' => $LOGGED_IN == "1"]|json_encode}</script>
<div id="user-profile"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_profile.js"}"></script>
{/if}

{include file='footer.tpl'}

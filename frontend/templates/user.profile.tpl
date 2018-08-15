{include file='head.tpl' htmlTitle="{#omegaupTitleProfile#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="profile">{$profile.userinfo|json_encode}</script>
<div id="user-profile"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_profile.js"}"></script>
{/if}

{include file='footer.tpl'}

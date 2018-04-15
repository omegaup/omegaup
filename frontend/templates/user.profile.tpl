{include file='head.tpl' htmlTitle="{#omegaupTitleProfile#}"}

{if !isset($STATUS_ERROR)}
<script type="text/json" id="profile">{$profile.userinfo|json_encode}</script></script>
<div id="user-profile"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_profile.js"}"></script>
<script src="{version_hash src="/third_party/js/iso-3166-2.js/iso3166.min.js"}"></script>
{/if}

{include file='footer.tpl'}

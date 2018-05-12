{include file='head.tpl' htmlTitle="{#omegaupTitlePrivacyPolicies#}"}

<script type="text/json" id="payload">{$PRIVACY_POLICIES|json_encode}</script></script>
<div id="privacy-policies"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_privacy_poilicies.js"}"></script>

{include file='footer.tpl'}
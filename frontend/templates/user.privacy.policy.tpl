{include file='head.tpl' htmlTitle="{#omegaupTitlePrivacyPolicy#}" inPolicy=true}

<script type="text/json" id="payload">{$PRIVACY_POLICY|json_encode}</script></script>
<div id="privacy-policy"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/user_privacy_poilicy.js"}"></script>

{include file='footer.tpl'}
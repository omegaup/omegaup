{include file='head.tpl' recaptchaFile='https://www.google.com/recaptcha/api.js' htmlTitle="{#omegaupTitleLogout#}" inline}

<script type="text/javascript" src="{version_hash src="/js/logout.js"}" defer></script>
{if $GOOGLECLIENTID != ""}
<script src="https://apis.google.com/js/platform.js" async defer></script>
{else}
{/if}
{include file='footer.tpl' inline}

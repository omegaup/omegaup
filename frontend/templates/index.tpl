{include file='head.tpl' htmlTitle="{#omegaupTitleIndex#}"}

{if isset($coderOfTheMonthData)}
<script type="text/json" id="coder-of-the-month-payload">{$coderOfTheMonthData|json_encode}</script>
{else}
<script type="text/json" id="coder-of-the-month-payload">null</script>
{/if}
{if $LOGGED_IN eq '1'}
<script type="text/json" id="current-user-payload">{$currentUserInfo|json_encode}</script>
{else}
<script type="text/json" id="current-user-payload">null</script>
{/if}
<script type="text/javascript" src="{version_hash src="/js/dist/coder_of_the_month_notice.js"}" async></script>
<div id="coder-of-the-month-notice"></div>



<div id="landing-page"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/common_landing_page.js"}"></script>
<script type="text/javascript" src="{version_hash src="/js/index.js"}" async></script>

{include file='footer.tpl'}

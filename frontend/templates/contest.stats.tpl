{include file='redirect.tpl' inline}
{include file='head.tpl' htmlTitle="{#omegaupTitleContestStats#}" inline}

<div id="common-stats"></div>
<script type="text/json" id="stats-payload">{$statsPayload|json_encode}</script>
{js_include entrypoint="common_stats"}

{include file='footer.tpl' inline}

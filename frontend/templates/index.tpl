{include file='head.tpl' htmlTitle="{#omegaupTitleIndex#}" inline}

<script type="text/json" id="payload">{$payload|json_encode}</script>
{js_include entrypoint="common_index" async}
<div id="common-index"></div>

<script type="text/javascript" src="{version_hash src="/js/index.js"}" async></script>
<script type="text/json" id="runs-chart-payload">{$runsChartPayload|json_encode}</script>
{js_include entrypoint="common_runs_chart" async}

{include file='footer.tpl' inline}

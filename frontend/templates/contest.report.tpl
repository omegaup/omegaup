{include file='head.tpl' htmlTitle="Reporte {$smarty.get.contest_alias}"}
<script type="text/json" id="payload">{['contestReport' => $contestReport]|json_encode}</script>
<div id="contest-report"></div>
<script type="text/javascript" src="{version_hash src="/js/dist/contest_report.js"}"></script>
<link rel="stylesheet" href="/css/report.css" />
{include file='footer.tpl'}
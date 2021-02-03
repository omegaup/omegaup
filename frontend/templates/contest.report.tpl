{include file='head.tpl' htmlTitle="Reporte {$smarty.get.contest_alias}" inline}
<script type="text/json" id="payload">{['contestReport' => $contestReport]|json_encode}</script>
<div id="contest-report"></div>
{js_include entrypoint="contest_report"}
<link rel="stylesheet" href="/css/report.css" />
{include file='footer.tpl' inline}
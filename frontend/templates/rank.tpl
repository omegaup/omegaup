{include file='head.tpl' navbarSection='rank' headerPayload=$headerPayload htmlTitle="{#omegaupTitleRank#}" inline}
<script type="text/json" id="rank-table-payload">{$rankTablePayload|json_encode}</script>
<div id="rank-table"></div>
{js_include entrypoint="rank_table" async}
{include file='footer.tpl' inline}

{include file='head.tpl' navbarSection='rank' headerPayload=$headerPayload htmlTitle="{#omegaupTitleRank#}" inline}
<script type="text/json" id="school-rank-payload">{$schoolRankPayload|json_encode}</script>
{js_include entrypoint="schools_rank"}
<div id="omegaup-schools-rank"></div>
{include file='footer.tpl' inline}

{include file='head.tpl' navbarSection='rank' htmlTitle="{#omegaupTitleRank#}" inline}
<div class=" panel panel-default">
		<script type="text/json" id="schools-rank-payload">{$schoolRankPayload|json_encode}</script>
		<script type="text/javascript" src="{version_hash src="/js/dist/schools_rank.js"}"></script>
		<div id="omegaup-schools-rank"></div>
</div>
{include file='footer.tpl' inline}
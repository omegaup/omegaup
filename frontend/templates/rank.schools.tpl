{include file='head.tpl' navbarSection='rank' htmlTitle="{#omegaupTitleRank#}" inline}
<div class=" panel panel-default">
		<script type="text/json" id="schools-rank-payload">{$schoolRankPayload|json_encode}</script>
		{js_include entrypoint="schools_rank"}
		<div id="omegaup-schools-rank"></div>
</div>
{include file='footer.tpl' inline}
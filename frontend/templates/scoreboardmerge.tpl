{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle='{#omegaupTitleScoreboardmerge#}'}

<div class="post">
	<div class="copy">
		<legend>Concurso: <select class="contests" name='contests' id='contests' multiple="multiple" size="10">
		</select></legend>
	</div>

	<div class="POS Boton" id="get-merged-scoreboard">Ver scoreboard total</div>
</div>

<div class="post">
	<div class="copy" id="ranking">

	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/scoreboardmerge.js"}"></script>

{include file='footer.tpl'}

{include file='head.tpl' jsfile=null inArena=true inline}
{js_include entrypoint="arena_admin"}
			<div id="title">
				<h1 class="contest-title">Envíos globales</h1>
			</div>
			<div id="runs">
				<table class="runs"></table>
			</div>
		</div>
		<div id="overlay">
			<script type="text/json" id="payload">{$payload|json_encode}</script>
			<div id="run-details"></div>
		</div>
{include file='common.analytics.tpl' inline}
	</body>
</html>

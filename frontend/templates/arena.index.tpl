{include file='arena.head.tpl' jsfile='/ux/arena.js'}
			<h1>Arena</h1>
			<table class="contest-list">
				<thead><tr>
					<th>Concurso</th>
					<th>Descripción</th>
					<th class="time">Inicio</th>
					<th class="time">Fin</th>
				</tr></thead>
				<tbody id="current-contests">
				</tbody>
			</table>
				
			<h2>Concursos pasados</h2>
			<table class="contest-list">
				<thead><tr>
					<th>Concurso</th>
					<th>Descripción</th>
					<th class="time">Inicio</th>
					<th class="time">Fin</th>
				</tr></thead>
				<tbody id="past-contests">
				</tbody>
			</table>
		</div>
		{if $OMEGAUP_GA_TRACK eq 1}
		<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '{$OMEGAUP_GA_ID}']);
		_gaq.push(['_trackPageview']);
		(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
		</script>
		{/if}
	</body>
</html>

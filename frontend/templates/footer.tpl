		<div class="post footer" style="padding: 5px; color:black; margin: 0px auto">
			&nbsp; <img alt="OmegaUp" style='width: 60px; padding:0px; margin:0px; -webkit-box-shadow:0px 0px;' src='/media/omegaup_curves.png'> es un lugar para mejorar tus habilidades de desarrollo de software.
		</div>
		<!-- .post footer -->
		<div class="bottom">
		</div>
	</div>
	<!-- #content -->
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
<!-- #wrapper -->
</body>
</html>

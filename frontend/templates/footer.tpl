		<div class="footer navbar" id="footer">
			<div class="navbar-header">
				<span class="navbar-brand"><img alt="OmegaUp" class="logo" src='/media/omegaup_curves.png'> {#frontPageFooter#}</span>
			</div>
			{if !$smarty.const.OMEGAUP_LOCKDOWN}
			<ul class="nav navbar-nav navbar-right">
				<li><a href='https://omegaup.com/hackathon/'>{#frontPageDevelopers#}</a></li>
			</ul>
			{/if}
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

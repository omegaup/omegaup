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
		<script type="text/javascript" src="{version_hash src="/js/google-analytics.js"}"></script>
		{/if}
<!-- #root -->
</body>
</html>

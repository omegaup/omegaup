		<hr>
		<div class="footer navbar" id="footer">
			<div class="row">

				<div class="col-md-6">
					<img alt="OmegaUp" class="logo" src="/media/omegaup_curves.png">
					<h5>{#frontPageFooter#}</h5>
					<h6><a href="mailto:hello@omegaup">hello@omegaup.com</a></h6>
					<div class="row">
						<div class="col-md-4">
							<!-- Facebook like button -->
							<div id="fb-root"></div>
							<div class="fb-like" data-href="https://www.facebook.com/omegaup" data-layout="button_count" data-action="like" data-height="20" data-show-faces="false" data-share="true"></div>
						</div>
						<div class="col-md-4">
							<!-- Twitter follow -->
							<a href="https://twitter.com/omegaup?ref_src=twsrc%5Etfw" class="twitter-follow-button" data-width="300px" data-height="20" data-show-screen-name="false" data-dnt="true" data-show-count="true">Follow @omegaup</a>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row">
						<div class="col-md-6">
							<h5><strong>{#frontPageFooterOrganization#}</strong></h5>
							<h6><a href="https://omegaup.org/#about" target="_blank">{#frontPageFooterAboutUs#}</a></h6>
							<h6><a href="https://omegaup.org/#team" target="_blank">{#frontPageFooterTeam#}</a></h6>
							<h6><a href="https://blog.omegaup.com/privacy-policy/" target="_blank">{#frontPageFooterPrivacyPolicy#}</a></h6>
						</div>
						<div class="col-md-6">
							<h5><strong>{#frontPageFooterDevelopers#}</strong></h5>
							<h6><a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar" target="_blank">{#frontPageFooterHelpUs#}</a></h6>
							<h6><a href="https://github.com/omegaup/omegaup" target="_blank">GitHub</a></h6>
{if !$smarty.const.OMEGAUP_LOCKDOWN && $LOGGED_IN eq '1'}
							<h6><a href="https://github.com/omegaup/omegaup/issues/new" target="_blank" rel="nofollow" id="report-an-issue">{#reportAnIssue#}</a></h6>
{/if}
						</div>
					</div>
				</div>

			</div>
		</div>
		<!-- #content -->
	</div>
</div>
{include file='common.analytics.tpl'}
<!-- #root -->
</body>
</html>

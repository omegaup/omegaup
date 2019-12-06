		<hr>
		<div class="footer navbar" id="footer">
			<div class="row">

				<div class="col-md-6">
					<img alt="OmegaUp" class="logo" src="/media/omegaup_curves.png">
					<h5>{#frontPageFooter#}</h5>
					<h6><a href="mailto:hello@omegaup.com">hello@omegaup.com</a></h6>
					<div class="row">
						<div class="col-md-12">
							{if $ENABLE_SOCIAL_MEDIA_RESOURCES}
							<!-- Facebook like button -->
							<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup&width=137&layout=button_count&action=like&size=small&show_faces=false&share=true&height=46&appId" width="190" height="46" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowTransparency="true" allow="encrypted-media"></iframe>
						</div>
						<div class="col-md-12">
							<!-- Twitter follow -->
							<a href="https://twitter.com/omegaup?ref_src=twsrc%5Etfw" class="twitter-follow-button" data-width="300px" data-height="20" data-show-screen-name="false" data-dnt="true" data-show-count="true">Follow @omegaup</a>
							<script async defer src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
							{/if}
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
{include file='common.analytics.tpl' inline}
<!-- #root -->
</body>
</html>

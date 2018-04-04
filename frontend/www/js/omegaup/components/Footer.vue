<template>
		<hr>
		<div class="footer navbar">
			<div class="row">

				<div class="col-md-6">
					<img alt="OmegaUp" class="logo" src="/media/omegaup_curves.png">
					<h5>{{T.frontPageFooter}}</h5>
					<h6><a href="mailto:hello@omegaup">hello@omegaup.com</a></h6>
					<div class="row">
						<div class="col-md-4">
							<!-- Facebook like button -->
							<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fomegaup&amp;width&amp;layout=button_count&amp;action=like&amp;show_faces=false&amp;share=true&amp;height=21&amp;appId=197705690257857" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:21px;" allowTransparency="true"></iframe>
						</div>
						<div class="col-md-4">
							<!-- Twitter follow -->
								<iframe allowtransparency="true" frameborder="0" scrolling="no"
									src="https://platform.twitter.com/widgets/follow_button.html?screen_name=omegaup"
									style="width:300px; height:20px;"></iframe>
						</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="row">
					<div class="col-md-6">
						<h5><strong>{{T.frontPageFooterOrganization}}</strong></h5>
						<h6><a href="https://omegaup.org/#about" target="_blank">{{T.frontPageFooterAboutUs}}</a></h6>
						<h6><a href="https://omegaup.org/#team" target="_blank">{{T.frontPageFooterTeam}}</a></h6>
						<h6><a href="https://blog.omegaup.com/privacy-policy/" target="_blank">{{T.frontPageFooterPrivacyPolicy}}</a></h6>
					</div>
					<div class="col-md-6">
						<h5><strong>{{T.frontPageFooterDevelopers}}</h5></strong>
						<h6><a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-empezar-a-desarrollar" target="_blank">{{T.frontPageFooterHelpUs}}</a></h6>
						<h6><a href="https://github.com/omegaup/omegaup" target="_blank">Github</a></h6>
					</div>
				</div>
			</div>

		</div>
	</div>
	<!-- #content -->
</div>
</template>

<script>
import {T} from '../omegaup.js';
export default {
	data: function() {
		return {
			T: T,
		};
	}
};
</script>
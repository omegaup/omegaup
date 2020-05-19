<template>
  <div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">{{T.loginSignupHeader}}</h2>
		</div>
		<div class="panel-body">
			<form method='POST' action='/login/'>
				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						<div class="form-group">
							<label class="control-label">{{T.wordsUser}}</label>
							<input name="reg_username" value="" type="text" class="form-control" autocomplete="username">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">{{T.loginEmail}}</label>
							<input name="reg_email" value="" type="email" class="form-control" autocomplete="email">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						<div class="form-group">
							<label class="control-label">{{T.loginPasswordCreate}}</label>
							<input name="reg_pass" value="" type="password" class="form-control" autocomplete="new-password">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label class="control-label">{{T.loginRepeatPassword}}</label>
							<input name="reg_pass2" value="" type="password" class="form-control" autocomplete="new-password">
						</div>
					</div>
				</div>
				<div class="row">
					<div v-html="T.privacyPolicyNotice" class="col-md-4 col-md-offset-2">
					</div>
					<div class="col-md-4">
						<div  v-if="true" class="g-recaptcha" data-sitekey="6LfMqdoSAAAAALS8h-PB_sqY7V4nJjFpGK2jAokS"></div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 col-md-offset-6">
						<div class="form-group">
							<button class="btn btn-primary form-control" type='submit'>{{T.loginSignUp}}</button>
							<input name="request" value="register" type="hidden">
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as UI from '../../ui';

@Component
export default class PasswordRecover extends Vue {
  T = T;
  UI =UI;
}
</script>
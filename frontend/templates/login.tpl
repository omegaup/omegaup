{include file='head.tpl' recaptchaFile='https://www.google.com/recaptcha/api.js' htmlTitle="{#omegaupTitleLogin#}"}

<div id="login-page">
	<script type="text/json" id="payload">{$payload|json_encode}</script>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">{#loginHeader#}</h2>
		</div>

		<div class="row">
			<div class="col-md-4 col-md-offset-2">
				<h4>{#loginFederated#}</h4>

				<div
					id="signinButton"
					title="{#loginWithGoogle#}"
					class="openid_large_btn">
					<span class="g-signin "
						data-scope="email"
						data-clientid="{$GOOGLECLIENTID}"
						data-redirecturi="postmessage"
						data-cookiepolicy="single_host_origin"
						data-callback="signInCallback">
					</span>
				</div>

				<a href="{$FB_URL}"
					title="{#loginWithFacebook#}"
					class="facebook openid_large_btn"></a>
				<a style="float:right"></a>

				<a href="{$LINKEDIN_URL}"
					 title="{#loginWithLinkedIn#}"
					 class="openid_large_btn">
					<img src="/media/third_party/LinkedIn-Sign-in-Small---Default.png" />
				</a>
			</div>

			<div class="col-md-4">
				<h4>{#loginNative#}</h4>
				<form method='POST' action='{$smarty.server.REQUEST_URI}' id='login_form' class="form-horizontal">
					<div class="form-group">
						<label for='user'>{#loginEmailUsername#}</label>
						<input id='user' name='user' value='' type='text' class='form-control' tabindex="1" />
					</div>

					<div class="form-group">
						<label for='pass'>{#loginPassword#} (<a href="/login/password/recover/">{#loginRecover#}</a>)</label>
						<input id='pass' name='pass' value='' type='password' class='form-control' tabindex="2" />
					</div>

					<input name="request" value="login" type="hidden" />

					<div class="form-group">
						<button class="btn btn-primary form-control" type='submit'>{#loginLogIn#}</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">{#loginSignupHeader#}</h2>
		</div>
		<div class="panel-body">
			<form method='POST' action='/login/' id="register-form">
				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						<div class="form-group">
							<label for="reg_username" class="control-label">{#wordsUser#}</label>
							<input id='reg_username' name='reg_username' value='' type='text' class="form-control">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="reg_email" class="control-label">{#loginEmail#}</label>
							<input id='reg_email' name='reg_email' value='' type='email' class="form-control">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						<div class="form-group">
							<label for="reg_pass" class="control-label">{#loginPasswordCreate#}</label>
							<input id='reg_pass' name='reg_pass' value='' type='password' class="form-control">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="reg_pass2" class="control-label">{#loginRepeatPassword#}</label>
							<input id='reg_pass2' name='reg_pass2' value='' type='password' class="form-control">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						{#privacyPolicyNotice#}
					</div>
					<div class="col-md-4">
						{if $VALIDATE_RECAPTCHA}
						<div class="g-recaptcha" data-sitekey="6LfMqdoSAAAAALS8h-PB_sqY7V4nJjFpGK2jAokS"></div>
						{/if}
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 col-md-offset-6">
						<div class="form-group">
							<button class="btn btn-primary form-control" type='submit'>{#loginSignUp#}</button>
							<input name="request" value="register" type="hidden">
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript" src="{version_hash src="/js/login.js"}"></script>
<script src="https://apis.google.com/js/platform.js?onload=renderButton" defer></script>
{include file='footer.tpl'}

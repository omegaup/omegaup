{include file='head.tpl' recaptchaFile='https://www.google.com/recaptcha/api.js' htmlTitle="{#omegaupTitleLogin#}" inline}

<div id="login-page">
	<script type="text/json" id="payload">{$payload|json_encode}</script>
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">{#loginHeader#}</h2>
		</div>

		<div class="row">
			<div class="col-md-4 col-md-offset-2">
				<h4>{#loginFederated#}</h4>
				<div class="row">

{if $GOOGLECLIENTID != ""}
					<div class="col-xs-12 col-md-4 text-center py-2">
						<div id="google-signin" title="{#loginWithGoogle#}"></div>
					</div>
{/if}

					<div class="col-xs-12 col-md-4 text-center py-2">
						<a href="{$FB_URL}" title="{#loginWithFacebook#}">
							<img src="/css/fb-oauth.png" height="45px" width="45px">
						</a>
					</div>

					<div class="col-xs-12 col-md-4 text-center py-2">
						<a href="{$LINKEDIN_URL}" title="{#loginWithLinkedIn#}">
							<img src="/css/ln-oauth.png" height="45px" width="45px">
						</a>
					</div>
				</div>
			</div>

			<div class="col-md-4">
				<h4>{#loginNative#}</h4>
				<form method='POST' action='{$smarty.server.REQUEST_URI}' id='login_form' class="form-horizontal">
					<div class="form-group">
						<label for="user">{#loginEmailUsername#}</label>
						<input id="user" name="user" value="" type="text" class="form-control" tabindex="1" autocomplete="username" />
					</div>

					<div class="form-group">
						<label for="pass">{#loginPassword#} (<a href="/login/password/recover/">{#loginRecover#}</a>)</label>
						<input id="pass" name="pass" value="" type="password" class="form-control" tabindex="2" autocomplete="current-password" />
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
							<input id="reg_username" name="reg_username" value="" type="text" class="form-control" autocomplete="username">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="reg_email" class="control-label">{#loginEmail#}</label>
							<input id="reg_email" name="reg_email" value="" type="email" class="form-control" autocomplete="email">
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						<div class="form-group">
							<label for="reg_pass" class="control-label">{#loginPasswordCreate#}</label>
							<input id="reg_pass" name="reg_pass" value="" type="password" class="form-control" autocomplete="new-password">
						</div>
					</div>

					<div class="col-md-4">
						<div class="form-group">
							<label for="reg_pass2" class="control-label">{#loginRepeatPassword#}</label>
							<input id="reg_pass2" name="reg_pass2" value="" type="password" class="form-control" autocomplete="new-password">
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						{#privacyPolicyNoticeDeprecated#}
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

<script type="text/javascript" src="{version_hash src="/js/login.js"}" defer></script>
{if $GOOGLECLIENTID != ""}
<script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>
{/if}
{include file='footer.tpl' inline}

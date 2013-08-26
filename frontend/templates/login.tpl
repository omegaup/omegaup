{include file='head.tpl'}
{include file='mainmenu.tpl'}
<div id="login-page">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">{#loginHeader#}</h3>
		</div>
		
		<div class="row">
			<div class="col-md-4 col-md-offset-2">
				<h4>{#loginFederated#}</h4>
				<a href="google.php{if $smarty.server.QUERY_STRING}?{$smarty.server.QUERY_STRING}{/if}" title="log in with Google" style="background: #fff url(https://cdn.sstatic.net/Img/openid/openid-logos.png?v=8); background-position: -1px -1px" class="google openid_large_btn"></a>
				&nbsp;&nbsp;&nbsp; <a href="{$FB_URL}" title="log in with Facebook" style="background: #fff url(https://cdn.sstatic.net/Img/openid/openid-logos.png?v=8); background-position: -1px -456px" class="facebook openid_large_btn"></a><a style="float:right"></a><br>
			</div>
		
			<div class="col-md-4">
				<h4>{#loginNative#}</h4>
				<form method='POST' action='{$smarty.server.REQUEST_URI}' id='login_form' class="form-horizontal">
					<div class="form-group">
						<div class="col-md-12">
							<input id='user' name='user' value='' type='text' class="form-control" placeholder="{#loginEmailUsername#}" />
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-12">
							<input id='pass' name='pass' value='' type='password' class="form-control" placeholder="{#loginPassword#}" />
						</div>
					</div>
					
					<input id='' name='request' value='login' type='hidden' />
					
					<div class="form-group">
						<div class="col-md-6">
							<a href="login.php" class="btn btn-link col-md-4">{#loginRecover#}</a>
						</div>
						<div class="col-md-6">
							<button class="btn btn-primary form-control" type='submit'>{#logIn#}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<div class="panel panel-default">
		<div class="panel-heading">
			<h2 class="panel-title">{#loginSignupHeader#}</h3>
		</div>
		<div class="panel-body">
			<form method='POST' action='login.php' id="register-form">
				<div class="row">
					<div class="col-md-4 col-md-offset-2">
						<div class="form-group">
							<label for="reg_username" class="control-label">{#loginUsername#}</label>
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
							<label for="reg_pass" class="control-label">{#loginPassword#}</label>
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
					<div class="col-md-4 col-md-offset-6">
						<div class="form-group">
							<button class="btn btn-primary form-control" type='submit'>Registrar</button>
							<input id='' name='request' value='register' type='hidden'>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script>
	function registerAndLogin(){
		omegaup.createUser(
			$('#reg_email').val(),
			$('#reg_username').val(),
			$('#reg_pass').val(),
			function (data) { 
				//registration callback
				//test data ok
				$("#user").val($('#reg_email').val());
				$("#pass").val($('#reg_pass').val());
				$("#login_form").submit();
			}
		);
		return false; // Prevent form submission
	}
	
	$("#register-form").submit(registerAndLogin);
</script>

{include file='footer.tpl'}

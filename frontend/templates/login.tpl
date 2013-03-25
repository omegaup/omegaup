{if $LOGGED_IN eq '1'} 
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
	<html>
	<head>
	<title>Omegaup</title>
	<meta http-equiv="REFRESH" content="0;url=/profile.php"></HEAD>
		<body>
		</body>
	</HTML>
{/if}

{include file='head.tpl'}
{include file='mainmenu.tpl'}
<div class="post">
	<div class="copy">
		<h1 class="subheader">{#loginHeader#}</h1>
		<table style="width:100%">
		<tr >
			<td valign=top>
				<h3>{#loginFederated#}</h3>
				<a href="google.php" title="log in with Google" style="background: #fff url(https://cdn.sstatic.net/Img/openid/openid-logos.png?v=8); background-position: -1px -1px" class="google openid_large_btn"></a>
				&nbsp;&nbsp;&nbsp; <a href="{$FB_URL}" title="log in with Facebook" style="background: #fff url(https://cdn.sstatic.net/Img/openid/openid-logos.png?v=8); background-position: -1px -456px" class="facebook openid_large_btn"></a><a style="float:right"></a><br>
			</td>

			<td>
				<h3>{#loginNative#}</h3>
				<div>
					<form method='POST' action='login.php'>
					<table width='100%' >
						<tr>
							<td>{#loginEmailUsername#}</td>
							<td>
								<input id='user' name='user' value='' type='input'>
							</td>
						</tr>
						<tr>
							<td>{#loginPassword#}</td>
							<td>
								<input id='pass' name='pass' value='' type='password'>
							</td>
						</tr>
						<tr>
							<input id='' name='request' value='login' type='hidden'>
						</tr>
						<tr>
							<td>
							</td>
							<td align='right'>
								<input value='{#loginRecover#}' type='submit'>
								<input value='{#logIn#}' type='submit'>
							</td>
						</tr>
					</table>
					</form>
				</div>
			</td>
		</tr>
		</table>
	</div>
	<div class="copy">
		<h3 style=''>{#loginSignupHeader#}</h3>
		<div>
			<form method='POST' action='login.php'>
			<table width='100%'>
				<tr>
					<td>{#loginUsername#}</td>
					<td>
						<input id='reg_username' name='username' value='' type='input'>
					</td>
					<td>{#loginEmail#}</td>
					<td>
						<input id='reg_email' name='email' value='' type='input'>
					</td>
				</tr>
				<tr>
					<td>{#loginPassword#}</td>
					<td>
						<input id='reg_pass' name='pass' value='' type='password'>
					</td>
					<td>{#loginRepeatPassword#}</td>
					<td>
						<input id='reg_pass2' name='pass2' value='' type='password'>
					</td>
				</tr>
				<tr>
					<input id='' name='request' value='register' type='hidden'>
				</tr>
				<tr>
					<td>
					</td>
					<td>
						</td>
					<td>
					</td>
					<td align='right'>
						<input value='Registrar' type='button' onClick="omegaup.createUser( $('#reg_email').val(), $('#reg_username').val(), $('#reg_pass').val(), function(){ window.location = '/login.php'; } )">
					</td>
				</tr>
			</table>
			</form>
		</div>
	</div>
</div>

{include file='footer.tpl'}

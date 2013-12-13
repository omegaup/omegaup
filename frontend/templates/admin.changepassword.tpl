{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleAdminChangePassword#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">Force change passwords</h2>
	</div>
	<div class="panel-body">
		<form class="form bottom-margin" id="change-password-form">	
			<div class="form-group">
				<label for="username">Username</label>
				<input id='username' name='username' value='' type='text' size='20' class="form-control" />
			</div>
			<input id='user' name='user' value='' type='hidden'>
			
			<div class="form-group">
				<label for="password">New password</label>
				<input id='password' name='password' value='' type='text' size='20' class="form-control" />
			</div>
			
			<button class="btn btn-primary" type='submit'>Change password</button>
		</form>
	</div>
</div>
				
<script>
	$("#username").typeahead({
		ajax: "/api/user/list/",
		display: 'label',
		val: 'label',
		minLength: 2,
		itemSelected: function (item, val, text) {
			$("#user").val(val);
		}
    });
	
	$('#change-password-form').submit(function() {
		password = $('#password').val();
		username = $("#user").val();
		
		omegaup.forceChangePassword(username, password, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success("Password successfully changed!");
				$('div.post.footer').show();
				
				updateContestUsers();
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});
		return false; // Prevent refresh
	});
</script>

{include file='footer.tpl'}

{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">Force user validation</h2>
	</div>
	<div class="panel-body">
		<form class="form bottom-margin" id="verify-user-form">	
			<div class="form-group">
				<label for="username">Username</label>
				<input id='username' name='username' value='' type='text' size='20' class="form-control" />
			</div>
			
			<button class="btn btn-primary" type='submit'>Verify user</button>
		</form>		
	</div>
</div>

<script>
	$("#username").typeahead({
		minLength: 2,
		highlight: true,
	}, {
		source: omegaup.searchUsers,
		displayKey: 'label',
	}).on('typeahead:selected', function(item, val, text) {
		$("#username").val(val.label);
	});
	
	$('#verify-user-form').submit(function() {		
		username = $("#username").val();
		
		omegaup.forceVerifyEmail(username, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success("User successfully verified!");
				$('div.post.footer').show();								
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});
		return false; // Prevent refresh
	});
	
</script>

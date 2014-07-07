{extends file="profile.tpl"} 
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">{#userEditEditProfile#}</h2>
			</div>
			<div class="panel-body">
				<form id="user_profile_form" class="form-horizontal" role="form">
					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#profileUsername#}</label>
						<div class="col-md-7">
							<input id='username' name='username' value='' type='text' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#profile#}</label>
						<div class="col-md-7">
							<input id='name' name='name' value='' type='text' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-1' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<label for="name" class="col-md-3 control-label">{#userEditChangePasswordRepeatNewPassword#}</label>
						<div class="col-md-7">
							<input id='new-password-2' name='name' value='' type='password' size='30' class="form-control">
						</div>
					</div>

					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">{#wordsSaveChanges#}</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<script>
{literal}
		
		omegaup.getProfile(null, function(data) {
			$("#username").val(data.userinfo.username);
			$("#name").val(data.userinfo.name);
		});
		
		var formSubmit = function() {

			var newPassword = $('#new-password-1').val();
			var newPassword2 = $('#new-password-2').val();
			if (newPassword != newPassword2) {
				OmegaUp.ui.error("Los passwords nuevos deben ser iguales.");
				return false;
			}

			omegaup.updateBasicProfile($("#username").val(), 
								  $("#name").val(), 
								  $("#new-password-1").val(), 
								  function(response){
									if (response.status == "ok") {
										window.location = "/profile/";
										return false;
									}
									else if(response.error !== undefined){
										OmegaUp.ui.error(response.error);
									}
			});
			return false; // Prevent page refresh on submit
		};

		$('form#user_profile_form').submit(formSubmit);

{/literal}		
	</script>
	

{/block}


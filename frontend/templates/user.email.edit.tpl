{extends file="profile.tpl"} 
{block name="content"}
	<div class="col-md-10">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h2 class="panel-title">Edita tu email principal</h2>
			</div>
			<div class="panel-body">
				<form id="user_edit_email_form" class="form-horizontal" role="form">
					
					<div class="form-group">
						<label for="email" class="col-md-3 control-label">Email</label>
						<div class="col-md-7">
							<input id='email' name='email' value='{$CURRENT_USER_EMAIL}' type='text' size='30' class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-md-offset-3 col-md-7">
							<button type='submit' class="btn btn-primary">Guardar cambios</button>							
						</div>
					</div>
					
				</form>
				<div id="wait" style="display: none;">Guardando <img src="/media/wait.gif" /></div>
			</div>
		</div>
	</div>
							
	<script>
		$('form#user_edit_email_form').submit(function (){
			
			$('#wait').show();
			
			omegaup.updateMainEmail($('#email').val(), function (response) {
				if (response.status == "ok") {
					$('#status').html("Email actualizado correctamente! En unos minutos recibirás más instrucciones en tu email. No olvides revisar tu carpeta de Spam.");
					$('#status').addClass("alert-success");
					$('#status').slideDown();
					
					$('#wait').hide();
					return false;
				} else {
					OmegaUp.ui.error(response.error || 'error');					
				}
				
				$('#wait').hide();
			});
									
			// Prevent page refresh on submit
			return false;
		});
	</script>
{/block}
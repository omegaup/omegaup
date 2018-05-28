<template>
		<div class="col-md-10">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h2 class="panel-title">{{ T.userEmailEditEmail }}</h2>
				</div>
				<div class="panel-body">
					<form id="user_edit_email_form" class="form-horizontal" role="form">

						<div class="form-group">
							<label for="email" class="col-md-3 control-label">{{ T.userEmailEditEmail }}</label>
							<div class="col-md-7">
								<input id='email' name='email' v-bind:value=profile.email type='text' size='30' class="form-control">
							</div>
						</div>

						<div class="form-group">
							<div class="col-md-offset-3 col-md-7">
								<button type='submit' class="btn btn-primary">{{ T.wordsSaveChanges }}</button>
							</div>
						</div>

					</form>
					<div id="wait" style="display: none;">{{ T.userEmailEditSaving }}<img src="/media/wait.gif" /></div>
				</div>
			</div>
	</div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
	props: {
		profile: Object,
	},
	
	data: function(){
		return {
			T:T,
		}
	}

}

</script>

{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContestAdduser#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<style>
	.ui-autocomplete-loading {
		background: white url('/media/waitcircle.gif') right center no-repeat;
	}
</style>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h2 class="panel-title">{#contestAdduserAddContestant#}</h2>
	</div>
	<div class="panel-body">
		<form class="form bottom-margin" id="add-user-form">
			<div class="form-group">
				<label for="contests">{#wordsContest#}</label>
				<select class='form-control' name='contests' id='contests'>
					<option value=""></option>				
				</select>
			</div>
			
			<div class="form-group">
				<label for="username">{#wordsUser#}</label>
				<input id='username' name='username' value='' type='text' size='20' class="form-control" />
			</div>

			<input id='user' name='user' value='' type='hidden'>

			<button class="btn btn-primary" type='submit'>Agregar {#wordsUser#}</button>
		</form>
		
		<div class="row">
			<div class="col-md-5">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h3 class="panel-title">{#contestAdduserRegisteredUsers#}</h3>
					</div>
					<table class="table table-striped">
						<thead>
							<th>{#contestAdduserRegisteredUserUsername#}</th>
							<th>{#contestAdduserRegisteredUserTime#}</th>
							<th>{#contestAdduserRegisteredUserDelete#}</th>
						</thead>
						<tbody id="contest-users"></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	function updateContestUsers() {
		contestAlias = $('select#contests').val();
		
		// Reset users list
		$('#contest-users').empty();
		
		omegaup.getContestUsers(contestAlias, function(users) {					
			// Got the contests, lets populate the dropdown with them
			for (var i = 0; i < users.users.length; i++) {
				user = users.users[i];
				$('#contest-users').append(
					$('<tr></tr>')
						.append($('<td></td>').append(
							$('<a></a>')
								.attr('href', '/profile/' + user.username + '/')
								.text(user.username)
						))
						.append($('<td></td>').text(user.access_time))
						.append($('<td><button type="button" class="close">&times;</button></td>')
							.click((function(contestAlias, username) {
								return function(e) {
									omegaup.removeUserFromContest(contestAlias, username, function(response) {
										if (response.status == "ok") {
											OmegaUp.ui.success("User successfully removed!");
											$('div.post.footer').show();
											var tr = e.target.parentElement.parentElement;
											$(tr).remove();
										} else {
											OmegaUp.ui.error(response.error || 'error');
										}
									});
								};
							})(contestAlias, user.username))
						)
				);
			}
		});	
	}
	
	omegaup.getMyContests(function(contests) {					
		// Got the contests, lets populate the dropdown with them			
		for (var i = 0; i < contests.results.length; i++) {
			contest = contests.results[i];							
			$('select#contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
		}				
		
		$('select#contests').change(function () {					
			updateContestUsers();
		});
		
		// If we have a contest in GET, then select it
		{IF isset($smarty.get.contest)}
		$('select#contests').each(function() {
			$('option', this).each(function() {
				if($(this).val() == "{$smarty.get.contest}") {
					$(this).attr('selected', 'selected');
					$('select#contests').trigger('change');
				}
			});
		});
		{/IF}
	});	
	
	$("#username").typeahead({
		ajax: "/api/user/list/",
		display: 'label',
		val: 'label',
		minLength: 2,
		itemSelected: function (item, val, text) {
			$("#user").val(val);
		}
    });
	
	$('#add-user-form').submit(function() {
		contestAlias = $('select#contests').val();
		username = $("#user").val();
		omegaup.addUserToContest(contestAlias, username, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success("User successfully added!");
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

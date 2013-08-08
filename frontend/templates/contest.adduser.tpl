{include file='redirect.tpl'}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<style>
	.ui-autocomplete-loading {
		background: white url('img/ajax_wait_autocomplete.gif') right center no-repeat;
	}
</style>

<div class="post">
	<div class="copy">

		<legend>Concurso: <select class='contests' name='contests' id='contest_alias'>
				<option value=""></option>				
				</select>
		</legend>

		<legend>Usuario: <input id='username' name='username' value='' type='text' size='20' />
		</legend>

		<input id='user' name='user' value='' type='hidden'>

		<input value='Agregar usuario' type='submit' class="OK">

	</div>
</div>

<script>
	
	omegaup.getMyContests(function(contests) {					
		// Got the contests, lets populate the dropdown with them			
		for (var i = 0; i < contests.results.length; i++) {
			contest = contests.results[i];							
			$('select.contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
		}				
		
		// If we have a contest in GET, then get it
		{IF isset($smarty.get.contest)}
		$('select.contests').each(function() {
			$('option', this).each(function() {
				if($(this).val() == "{$smarty.get.contest}") {
					$(this).attr('selected', 'selected');					
				}
			});
		});
		{/IF}
	});	
	
	$( "#username" ).autocomplete({
		source: "/api/user/list/",
		minLength: 2,
		select: function( event, ui ) {
			$("#user").val(ui.item.value);			
		}
    });
	
	$('input.OK').click(function() {
		
		contestAlias = $('select.contests').val();
		username = $("#user").val();
		omegaup.addUserToContest(contestAlias, username, function(response) {
			if (response.status == "ok") {
				$('div.copy.error').html("User successfully added!");
				$('div.post.footer').show();
				return;
			}
		});
	});
	
</script>

{include file='footer.tpl'}
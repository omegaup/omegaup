{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContestNew#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{include file='contest.new.form.tpl'}

<script>
	
	omegaup.getMyContests(function(contests) {					
		// Got the contests, lets populate the dropdown with them			
		for (var i = 0; i < contests.results.length; i++) {
			contest = contests.results[i];							
			$('select.contests').append($('<option></option>').attr('value', contest.alias).text(contest.title));
		}
		
		// Fill form on drop down change
		$('select.contests').change(function () {					
			$('div.post.footer').hide();
			refreshEditForm($('select.contests option:selected').val());
		});
		
		// If we have a contest in GET, then get it
		{IF isset($smarty.get.contest)}
		$('select.contests').each(function() {
			$('option', this).each(function() {
				if($(this).val() == "{$smarty.get.contest}") {
					$(this).attr('selected', 'selected');
					$('select.contests').trigger('change');
				}
			});
		});
		{/IF}
	});			
	
	$('.new_contest_form').submit(function() {
		
		var window_length_value = $('#window_length_enabled').is(':checked') ? 
				$('#window_length').val() : 
				'NULL';
		
		omegaup.updateContest (
			$("select.contests").val(),
			$(".new_contest_form #title").val(),
			$(".new_contest_form #description").val(),
			(new Date($(".new_contest_form #start_time").val()).getTime()) / 1000,
			(new Date($(".new_contest_form #finish_time").val()).getTime()) / 1000,
			window_length_value,
			$(".new_contest_form #alias").val(),
			$(".new_contest_form #points_decay_factor").val(),			
			$(".new_contest_form #submissions_gap").val() * 60,
			$(".new_contest_form #feedback").val(), 
			$(".new_contest_form #penalty").val(), 
			$(".new_contest_form #public").val(),
			$(".new_contest_form #scoreboard").val(), 
			$(".new_contest_form #penalty_time_start").val(),
			$(".new_contest_form #show_scoreboard_after").val(),
			function(data) {
				if(data.status == "ok") {
					OmegaUp.ui.success("Tu concurso ha sido editado! <a href='addproblemtocontest.php'>Agrégale problemas!</a> <a href='/arena/"+ $('.new_contest_form #alias').val() + "'>{#contestEditGoToContest#}</a>");
					$('div.post.footer').show();
					window.scrollTo(0,0);
				} else {
					OmegaUp.ui.error(data.error || 'error');
				}
			}
		);
		return false;
	 });
	 
	 function refreshEditForm(contestAlias) {
		if (contestAlias !== "") {
			omegaup.getContest(contestAlias, function(contest) {
				$(".new_contest_form #title").val(contest.title);
				$(".new_contest_form #alias").val(contest.alias);
				$(".new_contest_form #description").val(contest.description);
				$(".new_contest_form #start_time").val(dateToString(contest.start_time));
				$(".new_contest_form #finish_time").val(dateToString(contest.finish_time));
				
				if (contest.window_length === null) {					
					// Disable window length
					$('#window_length_enabled').removeAttr('checked');
					$('#window_length').attr('disabled','disabled');
					$('#window_length').val('');
				} else {
					$('#window_length_enabled').attr('checked', 'checked');
					$('#window_length').removeAttr('disabled');
					$('#window_length').val(contest.window_length);
				}
				
				$(".new_contest_form #points_decay_factor").val(contest.points_decay_factor);
				$(".new_contest_form #submissions_gap").val(contest.submissions_gap / 60);
				$(".new_contest_form #feedback").val(contest.feedback); 
				$(".new_contest_form #penalty").val(contest.penalty);
				$(".new_contest_form #public").val(contest.public);
				$(".new_contest_form #scoreboard").val(contest.scoreboard);
				$(".new_contest_form #penalty_time_start").val(contest.penalty_time_start);
				$(".new_contest_form #show_scoreboard_after").val(contest.show_scoreboard_after);
			});
		}
	 }

</script>

{include file='footer.tpl'}

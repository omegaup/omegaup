{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#omegaupTitleContestNew#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

{include file='contest.new.form.tpl'}

<script>
	$('.new_contest_form').submit(function() {
		var window_length_value = $('#window_length_enabled').is(':checked') ? 
				$('#window_length').val() : 
				'NULL';
		
		omegaup.createContest(
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
					OmegaUp.ui.success('{#contestNewCreatedContestSuccesfully#} <a href="contestedit.php?contest='+ $('.new_contest_form #alias').val() + '">{#contestEditAddProblems#}</a> <a href="/arena/'+ $('.new_contest_form #alias').val() + '">{#contestEditGoToContest#}</a>');
					window.scrollTo(0,0);
				} else {
					OmegaUp.ui.error(data.error || 'error');
				}
			}
		);
		return false;
	});
	
	// Toggle on/off window length on checkbox change
	$('#window_length_enabled').change(function() {
		if($(this).is(':checked')) {
			// Enable
			$('#window_length').removeAttr('disabled');	
		} else {
			// Disable
			$('#window_length').attr('disabled','disabled');
		}
	});
</script>

{include file='footer.tpl'}

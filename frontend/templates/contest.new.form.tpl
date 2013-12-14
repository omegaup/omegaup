{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}


<div class="panel panel-primary	">
	<div class="panel-heading">
		<h3 class="panel-title">
			{if $IS_UPDATE neq 1}
				{#contestNew#}
			{else}
				{#contestEdit#}
			{/if}
		</h3>
	</div>
	<div class="panel-body">
		{if $IS_UPDATE neq 1}
		<div class="btn-group bottom-margin">
			<button class="btn btn-default" id='omi' name='omi'>Estilo OMI - IOI</button>
			<button class="btn btn-default" id='preioi' name='preioi'>{#contestNewForm#}</button>
			<button class="btn btn-default" id='conacup' name='conacup'>{#contestNewFormConacupStyle#}</button>
		</div>		
		{/if}
		<form class="new_contest_form">
				{if $IS_UPDATE eq 1}
					<div class="row">
						<div class="form-group col-md-6">
							<label for="contests">{#contestNewFormContestToEdit#}</label>
							<select class='contests form-control' name='contests' id='contest_alias'>
								<option value=""></option>
							</select>
						</div>
					</div>
				{/if}
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">{#wordsTitle#}</label>
						<input id='title' name='title' value='' type='text' size='30' class="form-control">
					</div>
					
					<div class="form-group col-md-6">
						<label for="alias">{#contestNewFormShortTitle_alias_#}</label>
						<input id='alias' name='alias' value='' type='text' class="form-control" {IF $IS_UPDATE eq 1} disabled="true" {/if}>
						<p class="help-block">{#contestNewFormShortTitle_alias_Desc#}</p>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="start_time">{#contestNewFormNewFormStartDate#}</label>
						<input id='start_time' name='start_time' value='' class="form-control" type='text' size ='16'>
						<p class="help-block">{#contestNewFormStartDateDesc#}</p>
					</div>
					
					<div class="form-group col-md-6">
						<label for="finish_time">{#contestNewFormEndDate#}</label>
						<input id='finish_time' name='finish_time' value='' class="form-control" type='text' size='16'>
						<p class="help-block">{#contestNewFormEndDateDesc#}</p>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="description">{#contestNewFormDescription#}</label>
						<textarea id='description' name='description' cols="30" rows="10" class="form-control"></textarea>
					</div>
					
					<div class="form-group col-md-6">
						<label for="window_length_enabled">{#contestNewFormDifferentStarts#}</label>
						<div class="checkbox">
							<label>
								<input type='checkbox' id='window_length_enabled' name='window_length_enabled'>	Habilitar
							</label>
						</div>
						<input id='window_length' name='window_length' value='' type='text' disabled="true" size='3' class="form-control">
						<p class="help-block">{#contestNewFormDifferentStartsDesc#}</p>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="scoreboard">{#contestNewFormScoreboardTimePercent#}</label>
						<input id='scoreboard' name='scoreboard' value='100' type='text' size='3' class="form-control">
						<p class="help-block">{#contestNewFormScoreboardTimePercentDesc#}</p>
					</div>
					
					<div class="form-group col-md-6">
						<label for="submissions_gap">{#contestNewFormSubmissionsSeparation#}</label>
						<input id='submissions_gap' name='submissions_gap' value='1' type='text' size='2' class="form-control">
						<p class="help-block">{#contestNewFormDifferentStartsDesc#}</p>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="penalty_time_start">{#contestNewFormPenaltyType#}</label>
						<select name='penalty_time_start' id='penalty_time_start' class="form-control">
							<option value='none'>{#contestNewFormNoPenalty#}</option>
							<option value='problem'>{#contestNewFormByProblem#}</option>
							<option value='contest'>{#contestNewFormByContests#}</option>
						</select>
						<p class="help-block">Indica el momento cuando se inicia a contar el tiempo: cuando inicia el concurso o cuando se abre el problema.</p>
					</div>
					
					<div class="form-group col-md-6">
						<label for="penalty">{#wordsPenalty#}</label>
						<input id='penalty' name='penalty' value='0' type='text' size='2' class="form-control">
						<p class="help-block">{#contestNewFormPenaltyDesc#}</p>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="feedback">{#wordsFeedback#}</label>
						<select name='feedback' id='feedback' class="form-control">
							<option value='yes'>{#wordsYes#}</option>
							<option value='no'>{#wordsNo#}</option>
							<option value='partial'>{#wordsPartial#}</option>
						</select>
						<p class="help-block">{#contestNewFormImmediateFeedbackDesc#}</p>
					</div>
					
					<div class="form-group col-md-6">
						<label for="points_decay_factor">{#contestNewFormPintDecrementFactor#}</label>
						<input id='points_decay_factor' name='points_decay_factor' value='0.0' type='text' size='4' class="form-control">
						<p class="help-block">{#contestNewFormPintDecrementFactorDesc#}</p>
					</div>
				</div>
				
				<div class="row">
					<div class="form-group col-md-6">
						<label for="show_scoreboard_after">Scoreboard al finalizar el concurso</label>
						<select id='show_scoreboard_after' name='show_scoreboard_after' class="form-control">
							<option value='1'>{#wordsYes#}</option>
							<option value='0'>{#wordsNo#}</option>
						</select>
						<p class="help-block">{#contestNewFormScoreboardAtEndDesc#}</p>
					</div>
					
					<div class="form-group col-md-6">
						<label for="public">Público</label>
						<select name='public' id='public' class="form-control">
							<option value='1'>{#wordsYes#}</option>
							<option value='0'>{#wordsNo#}</option>
						</select>
						<p class="help-block">{#contestNewFormPublicDesc#}</p>
					</div>
				</div>
				
				<div class="form-group">
				{if $IS_UPDATE eq 1}
					<button type='submit' class="btn btn-primary">{#contestNewFormUpdateContest#}</button>
				{else}
					<button type='submit' class="btn btn-primary">{#contestNewFormScheduleContest#}</button>
				{/if}
				</div>
		</form>
	</div>
</div>
<script>		
	
	$("#start_time, #finish_time").datetimepicker({
		weekStart: 1,
		format: "mm/dd/yyyy hh:ii",
		startDate: Date.create(Date.now()),
	});
	
	{IF $IS_UPDATE neq 1}
		// Defaults for start_time and end_time	
		var defaultDate = Date.create(Date.now());
		defaultDate.set({ seconds: 0 });
		$('#start_time').val(dateToString(defaultDate));	
		defaultDate.setHours(defaultDate.getHours() + 5);
		$('#finish_time').val(dateToString(defaultDate));
	{/IF}
	
	$("#window_length_enabled").change(function () {
		if ($(this).is(":checked")) {
			$('#window_length').removeAttr('disabled');
		} else {
			$('#window_length').attr('disabled','disabled');
		}
	});
	
	// Defaults for OMI
	$('#omi').click(function() {
		$(".new_contest_form #title").val('**Estilo OMI aplicado. Pon tu título aquí**');
		$('#window_length_enabled').removeAttr('checked');
		$('#window_length').attr('disabled','disabled');
		$('#window_length').val('');
		
		$(".new_contest_form #public").val('1');
		$(".new_contest_form #scoreboard").val('0');
		$(".new_contest_form #points_decay_factor").val('0');		
		$(".new_contest_form #submissions_gap").val('1');
		$(".new_contest_form #feedback").val('yes');
		$(".new_contest_form #penalty").val('0');
		$(".new_contest_form #penalty_time_start").val('none');
		$(".new_contest_form #show_scoreboard_after").val('1');		
	});
	
	// Defaults for preselectivos IOI
	$('#preioi').click(function() {
		$(".new_contest_form #title").val('**Estilo Preselectivo aplicado. Pon tu título aquí**');
		$('#window_length_enabled').attr('checked', 'checked');
		$('#window_length').removeAttr('disabled');
		$('#window_length').val('180');
		
		$(".new_contest_form #public").val('1');
		$(".new_contest_form #scoreboard").val('0');
		$(".new_contest_form #points_decay_factor").val('0');		
		$(".new_contest_form #submissions_gap").val('0');
		$(".new_contest_form #feedback").val('yes');
		$(".new_contest_form #penalty").val('0');
		$(".new_contest_form #penalty_time_start").val('none');
		$(".new_contest_form #show_scoreboard_after").val('1');		
	});
	
	// Defaults for CONACUP
	$('#conacup').click(function() {
		$(".new_contest_form #title").val('**Estilo CONCACUP aplicado. Tu título aquí**');
		$('#window_length_enabled').removeAttr('checked');
		$('#window_length').attr('disabled','disabled');
		$('#window_length').val('');
		
		$(".new_contest_form #public").val('1');
		$(".new_contest_form #scoreboard").val('75');
		$(".new_contest_form #points_decay_factor").val('0');		
		$(".new_contest_form #submissions_gap").val('1');
		$(".new_contest_form #feedback").val('yes');
		$(".new_contest_form #penalty").val('20');
		$(".new_contest_form #penalty_time_start").val('contest');
		$(".new_contest_form #show_scoreboard_after").val('1');		
	});
</script>

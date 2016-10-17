{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<div class="panel panel-primary">
	{if $IS_UPDATE != 1}
	<div class="panel-heading">
		<h3 class="panel-title">
			{#contestNew#}
		</h3>
	</div>
	{/if}
	<div class="panel-body">
		{if $IS_UPDATE != 1}
		<div class="btn-group bottom-margin">
			<button class="btn btn-default" id='omi' name='omi'>{#contestNewFormOmiStyle#}</button>
			<button class="btn btn-default" id='preioi' name='preioi'>{#contestNewForm#}</button>
			<button class="btn btn-default" id='conacup' name='conacup'>{#contestNewFormConacupStyle#}</button>
		</div>
		{/if}
		<form class="new_contest_form">
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
						<label for="start_time">{#contestNewFormStartDate#}</label>
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
								<input type='checkbox' id='window_length_enabled' name='window_length_enabled'>	{#wordsEnable#}
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
						<p class="help-block">{#contestNewFormSubmissionsSeparationDesc#}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
						<label for="penalty_type">{#contestNewFormPenaltyType#}</label>
						<select name='penalty_type' id='penalty_type' class="form-control">
							<option value='none'>{#contestNewFormNoPenalty#}</option>
							<option value='problem_open'>{#contestNewFormByProblem#}</option>
							<option value='contest_start'>{#contestNewFormByContests#}</option>
							<option value='runtime'>{#contestNewFormByRuntime#}</option>
						</select>
						<p class="help-block">{#contestNewFormPenaltyTypeDesc#}</p>
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
						<label for="points_decay_factor">{#contestNewFormPointDecrementFactor#}</label>
						<input id='points_decay_factor' name='points_decay_factor' value='0.0' type='text' size='4' class="form-control">
						<p class="help-block">{#contestNewFormPointDecrementFactorDesc#}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
						<label for="show_scoreboard_after">{#contestNewFormScoreboardAtEnd#}</label>
						<select id='show_scoreboard_after' name='show_scoreboard_after' class="form-control">
							<option value='1'>{#wordsYes#}</option>
							<option value='0'>{#wordsNo#}</option>
						</select>
						<p class="help-block">{#contestNewFormScoreboardAtEndDesc#}</p>
					</div>

					{if $IS_UPDATE eq 1}
					<div class="form-group col-md-6">
						<label for="public">{#contestNewFormPublic#}</label>
						<select name='public' id='public' class="form-control">
							<option value='0' selected="selected">{#wordsNo#}</option>
							<option value='1'>{#wordsYes#}</option>
						</select>
						<p class="help-block">{#contestNewFormPublicDesc#}</p>
					</div>
					{/if}
				</div>

				{if $IS_UPDATE eq 1}
				<div class="row">
					<div class="form-group col-md-6">
						<label for="register">{#contestNewFormRegistration#}</label>
						<select name='register' id='register' class="form-control">
							<option value='0' selected="selected">{#wordsNo#}</option>
							<option value='1'>{#wordsYes#}</option>
						</select>
						<p class="help-block">{#contestNewFormRegistrationDesc#}</p>
					</div>
				</div>
				{/if}

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
<script type="text/javascript" src="{version_hash src="/js/contest.new.form.js"}"></script>

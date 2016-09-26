{assign var="htmlTitle" value="{#enterContest#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div id="intro-page" class="contest">
	<div class="panel panel-default">

		<div class="panel-heading">
			<h2 class="panel-title" >{#contestRules#}</h2>
		</div>

		<div class="row" >
			<div class="col-md-6 col-md-offset-1" >
				<div id="contest-details">
					<h2 id="title"></h2>
					<p id="description"></p>

					<div class="row">
						<div class="form-group col-md-6">
							<label title="{#contestNewFormStartDateDesc#}" for="start_time">{#contestNewFormStartDate#}</label>
							<input disabled id='start_time' name='start_time' value='' class="form-control" type='text' size ='16'>
						</div>

						<div class="form-group col-md-6">
							<label title="{#contestNewFormEndDateDesc#}" for="finish_time">{#contestNewFormEndDate#}</label>
							<input disabled id='finish_time' name='finish_time' value='' class="form-control" type='text' size='16'>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label title="{#contestNewFormScoreboardAtEndDesc#}" for="show_scoreboard_after">{#contestNewFormScoreboardAtEnd#}</label>
							<select disabled id='show_scoreboard_after' name='show_scoreboard_after' class="form-control">
								<option value='1'>{#wordsYes#}</option>
								<option value='0'>{#wordsNo#}</option>
							</select>
						</div>

						<div class="form-group col-md-6">
							<label title="{#contestNewFormDifferentStartsDesc#}" for="window_length_enabled">{#contestNewFormDifferentStarts#}</label>
							<div class="checkbox">
								<label>
									<input disabled type='checkbox' id='window_length_enabled' name='window_length_enabled'> {#wordsEnable#}
								</label>
							</div>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label title="{#contestNewFormScoreboardTimePercentDesc#}" for="scoreboard">{#contestNewFormScoreboardTimePercent#}</label>
							<input disabled id='scoreboard' name='scoreboard' value='100' type='text' size='3' class="form-control">
						</div>

						<div class="form-group col-md-6">
							<label title="{#contestNewFormSubmissionsSeparationDesc#}" for="submissions_gap">{#contestNewFormSubmissionsSeparation#}</label>
							<input disabled id='submissions_gap' name='submissions_gap' value='1' type='text' size='2' class="form-control">
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label title="{#contestNewFormPenaltyTypeDesc#}" for="penalty_type">{#contestNewFormPenaltyType#}</label>
							<select disabled name='penalty_type' id='penalty_type' class="form-control">
								<option value='none'>{#contestNewFormNoPenalty#}</option>
								<option value='problem_open'>{#contestNewFormByProblem#}</option>
								<option value='contest_start'>{#contestNewFormByContests#}</option>
								<option value='runtime'>{#contestNewFormByRuntime#}</option>
							</select>
						</div>

						<div class="form-group col-md-6">
							<label title="{#contestNewFormPenaltyDesc#}" for="penalty">{#wordsPenalty#}</label>
							<input disabled id='penalty' name='penalty' value='0' type='text' size='2' class="form-control">
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label  title="{#contestNewFormImmediateFeedbackDesc#}" for="feedback">{#wordsFeedback#}</label>
							<select disabled name='feedback' id='feedback' class="form-control">
								<option value='yes'>{#wordsYes#}</option>
								<option value='no'>{#wordsNo#}</option>
								<option value='partial'>{#wordsPartial#}</option>
							</select>
						</div>

						<div class="form-group col-md-6">
							<label title="{#contestNewFormPintDecrementFactorDesc#}" for="points_decay_factor">{#contestNewFormPintDecrementFactor#}</label>
							<input disabled id='points_decay_factor' name='points_decay_factor' value='0.0' type='text' size='4' class="form-control" />
						</div>
					</div>
				</div>
			</div><!-- contestRules -->

			<div class="col-md-4">
				<h4>{#contestJoin#}</h4>

{if $LOGGED_IN eq '1'}
				<!------------------- Wait for contest start -------------------------->
				<div id="ready_to_start" class="form-group hidden" >
					<p>{#contestWillBeginIn#} <span id="countdown_clock"></span></p>
				</div>

				<!------------------- Click to proceed -------------------------->
				<div id="click_to_proceed" class="form-group hidden" >
					<p>{#aboutToStart#}</p>
					<form id='start-contest-form' method="POST" action="/">
						<div class="form-group">
							<button type="submit" id="start-contest-submit" class="btn btn-primary form-control ">{#startContest#}</input>
						</div>
					</form>
				</div>

				<!------------------- Must register -------------------------->
				<div id="must_register" class="form-group hidden">
					<form id='request-access-form' method="POST" action="/foobar/">
						<div class="form-group">
							<p>{#mustRegisterToJoinContest#}</p>
						</div>

						<div class="form-group">
							<button type="submit" id="request-access-submit" class="btn btn-primary form-control ">{#registerForContest#}</input>
						</div>
					</form>
				</div>

				<!------------------- Registration pending -------------------------->
				<div id="registration_pending" class="form-group hidden">
						<div class="form-group">
							<p>{#registrationPending#}</p>
						</div>
				</div>

				<!------------------- Registration denied -------------------------->
				<div id="registration_denied" class="form-group hidden">
						<div class="form-group">
							<p>Denied!</p>
						</div>
				</div>
{else}
				<!------------------- Must login to do anything -------------------------->
				<div class="form-group">
					<p>{#mustLoginToJoinContest#}</p>
					<a href="/login/?redirect={$smarty.server.REQUEST_URI|escape:"url"}" class="btn btn-primary form-control ">{#loginHeader#}</a>
				</div>
{/if}

			</div><!-- contestJoin -->
		</div><!-- row -->
	</div><!-- panel panel-default -->

<script src="/js/contestintro.js?ver=c27cf2"></script>
{include file='footer.tpl'}


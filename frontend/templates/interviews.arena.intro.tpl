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
							<label title="{#contestNewFormScoreboardAtEndDesc#}" for="show_scoreboard_after">Scoreboard al finalizar el concurso</label>
							<select disabled id='show_scoreboard_after' name='show_scoreboard_after' class="form-control">
								<option value='1'>{#wordsYes#}</option>
								<option value='0'>{#wordsNo#}</option>
							</select>
						</div>

						<div class="form-group col-md-6">
							<label title="{#contestNewFormDifferentStartsDesc#}" for="window_length_enabled">{#contestNewFormDifferentStarts#}</label>
							<div class="checkbox">
								<label>
									<input disabled type='checkbox' id='window_length_enabled' name='window_length_enabled'>	Habilitar
								</label>
							</div>
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

<script src="/js/interviews.arena.intro.js?ver=1b9632"></script>
{include file='footer.tpl'}


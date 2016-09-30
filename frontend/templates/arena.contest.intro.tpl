{assign var="htmlTitle" value="{#enterContest#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div id='intro-page' class='contest'>
	<div class='panel'>
		<div id='contest-details' class='text-center form-group'>
			<h2 id='title'></h2>
			<div class='form-group'>
				<span id='start_time' name='start_time'></span>
				<span>-</span>
				<span id='finish_time' name='finish_time'></span>
			</div>
{if $LOGGED_IN eq '1'}
			<!------------------- Wait for contest start -------------------------->
			<div id='ready_to_start' class='form-group hidden' >
				<p>{#contestWillBeginIn#} <span id='countdown_clock'></span></p>
			</div>

			<!------------------- Click to proceed -------------------------->
			<div id='click_to_proceed' class='form-group hidden' >
				<form id='start-contest-form' method='POST' action='/'>
					<p>{#aboutToStart#}</p>
					<button type='submit' id='start-contest-submit' class='btn btn-primary btn-lg'>{#startContest#}</button>
				</form>
			</div>

			<!------------------- Must register -------------------------->
			<div id='must_register' class='form-group hidden'>
				<form id='request-access-form' method='POST' action='/foobar/'>
					<p>{#mustRegisterToJoinContest#}</p>
					<button type='submit' id='request-access-submit' class='btn btn-primary btn-lg'>{#registerForContest#}</button>
				</form>
			</div>
			<!------------------- Registration pending -------------------------->
			<div id='registration_pending' class='form-group hidden'>
				<p>{#registrationPending#}</p>
			</div>
			<!------------------- Registration denied -------------------------->
			<div id='registration_denied' class='form-group hidden'>
				<p>Denied!</p>
			</div>
{else}
			<!------------------- Must login to do anything -------------------------->
			<div class='form-group'>
				<p>{#mustLoginToJoinContest#}</p>
				<a href='/login/?redirect={$smarty.server.REQUEST_URI|escape:'url'}' class='btn btn-primary'>{#loginHeader#}</a>
			</div>
{/if}
		<hr>
		</div> <!-- div contest-details -->

		<div id='contest-description' class='container'>
			<h1>{#registerForContestChallenges#}</h1>
			<p id='description'></p>
		</div>
		<div id='contest-rules' class='container'>
			<h1>{#RegisterForContestRules#}</h1>
			<ul>
				<li id='show_scoreboard_after' name='show_scoreboard_after'></li>
				<li id='window_length_enabled' name='window_length_enabled'></li>
				<li id='scoreboard' name='scoreboard'></li>
				<li id='submissions_gap' name='submissions_gap'> 1 minute</li>
				<li id='penalty_type'></li>
				<li id='penalty' name='penalty'></li>
				<li id='feedback' name='feedback'></li>
				<li id='points_decay_factor' name='points_decay_factor'></li>
			</ul>
		</div>
	</div><!-- panel-->
</div>

<script src="/js/contestintro.js?ver=9b4940"></script>
<hr>
{include file='footer.tpl'}


{include file='head.tpl' jsfile={version_hash src='/js/contestintro.js'} htmlTitle="{#enterContest#}" loadMarkdown=true inline}

<div id="intro-page" class="contest panel hidden">
	<div class="panel-body">
		<div id="contest-details" class="text-center">
			<h2 id="title"></h2>
			<div class="">
				<span id="start-time" name="start_time"></span>
				<span>-</span>
				<span id="finish-time" name="finish_time"></span>
			</div>
{if $LOGGED_IN eq '1'}
			<!-- Wait for contest start -->
			<div id="ready-to-start" class="hidden" >
				<p>{#contestWillBeginIn#} <span id="countdown_clock"></span></p>
			</div>

			<!-- Click to proceed -->
			<div id="click-to-proceed" class="hidden" >
				<form id="start-contest-form" method="POST" action="/">
					{if !$payload.needsBasicInformation and $payload.requestsUserInformation == 'no'}
					    <p>{#aboutToStart#}</p>
					{/if}
					{if $payload.needsBasicInformation }
					    <p class="basic-information-needed">{#courseBasicInformationNeeded#}</p>
					{/if}
					{if $payload.requestsUserInformation != 'no'}
					 <script type="text/json" id="payload">{$payload.privacyStatement|json_encode}</script>
						<p class="requests-user-information requests-user-information-{$payload.requestsUserInformation}">
						</p>
						<p>
							<label>
								<input type="radio" name="share-user-information" value="1"> {#wordsYes#}
							</label>
							<label>
								<input type="radio" name="share-user-information" value="0"> {#wordsNo#}
							</label>
						</p>
					{/if}
					<button type="submit" id="start-contest-submit" class="btn btn-primary btn-lg"
					{if $payload.needsBasicInformation || $payload.requestsUserInformation != 'no'} disabled="true"{/if}>{#startContest#}</button>
				</form>
			</div>

			<!-- Must register -->
			<div id="must_register" class="hidden">
				<form id="request-access-form" method="POST" action="/foobar/">
					<p>{#mustRegisterToJoinContest#}</p>
					<button type="submit" id="request-access-submit" class="btn btn-primary btn-lg">{#registerForContest#}</button>
				</form>
			</div>
			<!-- Registration pending -->
			<div id="registration_pending" class="hidden">
				<p>{#registrationPending#}</p>
			</div>
			<!-- Registration denied -->
			<div id="registration_denied" class="hidden">
				<p>{#registrationDenied#}</p>
			</div>
{else}
			<!-- Must login to do anything -->
			<div class="panel">
				<p>{#mustLoginToJoinContest#}</p>
				<a href='/login/?redirect={$smarty.server.REQUEST_URI|escape:'url'}' class='btn btn-primary'>{#loginHeader#}</a>
			</div>
{/if}
		</div> <!-- div contest-details -->
		<hr>
		<div id="contest-description" class="">
			<h1>{#registerForContestChallenges#}</h1>
			<p id="description"></p>
		</div>
		<div id="contest-rules" class="">
			<h1>{#registerForContestRules#}</h1>
			<ul>
				<li id="show-scoreboard-after" name="show_scoreboard_after"></li>
				<li id="window-length-enabled" name="window_length_enabled"></li>
				<li id="scoreboard" name="scoreboard"></li>
				<li id="submissions-gap" name="submissions_gap"> 1 minute</li>
				<li id="penalty-type"></li>
				<li id="penalty" name="penalty"></li>
				<li id="feedback" name="feedback"></li>
				<li id="points-decay-factor" name="points_decay_factor"></li>
			</ul>
		</div>
	</div><!-- panel-->
</div>

{include file='footer.tpl' inline}


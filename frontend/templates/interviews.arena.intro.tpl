{include file='head.tpl' htmlTitle='{#enterContest#}'}
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
							<label for="window_length">{#wordsDuration#}</label>
							<select id='window_length' name='window_length' class="form-control" disabled>
								<option value="60">60 {#wordsMinutes#}</option>
								<option value="120">120 {#wordsMinutes#}</option>
								<option value="300">5 {#wordsHours#}</option>
							</select>
						</div>
					</div>

				</div>
			</div><!-- contestRules -->

			<div class="col-md-4">
				<h4>{#contestJoin#}</h4>

{if $LOGGED_IN eq '1'}
				<!------------------- Click to proceed -------------------------->
				<div id="click_to_proceed" class="form-group hidden" >
					<p>{#aboutToStart#}</p>
					<form id='start-contest-form' method="POST" action="/">
						<div class="form-group">
							<button type="submit" id="start-contest-submit" class="btn btn-primary form-control ">{#startContest#}</input>
						</div>
					</form>
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

<script src="{version_hash src="/js/interviews.arena.intro.js"}"></script>
{include file='footer.tpl'}


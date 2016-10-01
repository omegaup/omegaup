{assign var="htmlTitle" value="{#enterCourse#}"}
{include file='head.tpl'}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<div id="intro-page" class="course">
	<div class="panel panel-default">

		<div class="panel-heading">
			<h2 class="panel-title" >{#courseDetails#}</h2>
		</div>

		<div class="row" >
			<div class="col-md-6 col-md-offset-1" >
				<div id="course-details">
					<h2 id="title"></h2>
					<p id="description"></p>

					<div class="row">
						<div class="form-group col-md-6">
							<label title="{#courseNewFormStartDateDesc#}" for="start_time">{#courseNewFormStartDate#}</label>
							<input disabled id='start_time' name='start_time' value='' class="form-control" type='text' size ='16'>
						</div>

						<div class="form-group col-md-6">
							<label title="{#courseNewFormEndDateDesc#}" for="finish_time">{#courseNewFormEndDate#}</label>
							<input disabled id='finish_time' name='finish_time' value='' class="form-control" type='text' size='16'>
						</div>
					</div>

					<div class="row">
						<div class="form-group col-md-6">
							<label for="show_scoreboard">{#courseNewFormShowScoreboard#}</label>
							<select disabled name='show_scoreboard' id='show_scoreboard' class="form-control">
								<option value='1'>{#wordsYes#}</option>
								<option value='0'>{#wordsNo#}</option>
							</select>
						</div>
					</div>
			</div><!-- courseRules -->

			<div class="col-md-4">
				<h4>{#courseJoin#}</h4>

{if $LOGGED_IN eq '1'}
				<!------------------- Wait for course start -------------------------->
				<div id="ready_to_start" class="form-group hidden" >
					<p>{#courseWillBeginIn#} <span id="countdown_clock"></span></p>
				</div>
{else}
				<!------------------- Must login to do anything -------------------------->
				<div class="form-group">
					<p>{#mustLoginToJoinCourse#}</p>
					<a href="/login/?redirect={$smarty.server.REQUEST_URI|escape:"url"}" class="btn btn-primary form-control ">{#loginHeader#}</a>
				</div>
{/if}
			</div><!-- courseJoin -->
		</div><!-- row -->
	</div><!-- panel panel-default -->

<script src="/js/courseintro.js?ver=5e70ed"></script>
{include file='footer.tpl'}


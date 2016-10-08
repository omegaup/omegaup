{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<div class="panel">
	{if $IS_UPDATE != 1}
	<div class="panel-heading">
		<h3 class="panel-title">
			{#courseNew#}
		</h3>
	</div>
	{/if}
	<div class="panel-body">
		<form class="new_course_form">
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">{#wordsTitle#}</label>
						<input id='title' name='title' value='' type='text' size='30' class="form-control">
					</div>

					<div class="form-group col-md-6">
						<label for="alias" >{#courseNewFormShortTitle_alias_#}</label>
						<input id='alias' name='alias' value='' type='text' class="form-control" {IF $IS_UPDATE eq 1} disabled="true" {/if} >
						<p class="help-block">{#courseNewFormShortTitle_alias_Desc#}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
						<div class="form-group col-md-6">
							<label for="start_time">{#courseNewFormStartDate#}</label>
							<input id='start_time' name='start_time' value='' class="form-control" type='text' size ='16'>
						</div>
						<div class="form-group col-md-6">
							<label for="finish_time">{#courseNewFormEndDate#}</label>
							<input id='finish_time' name='finish_time' value='' class="form-control" type='text' size='16'>
						</div>
					<p class="help-block">{#courseNewFormEndDateDesc#}</p>
					</div>
					<div class="form-group col-md-6">
						<label for="show_scoreboard">{#courseNewFormShowScoreboard#}</label>
						<div class="form-control container">
							<label class="radio-inline"><input type="radio" id="show_scoreboard_2" name="show_scoreboard" value="1" checked=checked>{#wordsYes#}</label>
							<label class="radio-inline"><input type="radio" id="show_scoreboard_1" name="show_scoreboard" value="0">{#wordsNo#}</label>
						</div>
					</div>
					<p class="help-block">{#courseNewFormShowScoreboardDesc#}</p>
				</div>

				<div class="row">
					<div class="form-group container">
						<label for="description">{#courseNewFormDescription#}</label>
						<textarea id='description' name='description' cols="30" rows="5" class="form-control"></textarea>
					</div>
				</div>

				<div class="form-group">
				{if $IS_UPDATE eq 1}
					<button type='submit' class="btn btn-primary">{#courseNewFormUpdateCourse#}</button>
				{else}
					<button type='submit' class="btn btn-primary">{#courseNewFormScheduleCourse#}</button>
				{/if}
				</div>
		</form>
	</div>
</div>
<script type="text/javascript" src="/js/course.new.form.js?ver=da958d"></script>

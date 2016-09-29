{if !isset($IS_UPDATE)}
	{assign "IS_UPDATE" 0}
{/if}

<div class="panel panel-primary">
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
						<label for="alias">{#courseNewFormShortTitle_alias_#}</label>
						<input id='alias' name='alias' value='' type='text' class="form-control" {IF $IS_UPDATE eq 1} disabled="true" {/if}>
						<p class="help-block">{#courseNewFormShortTitle_alias_Desc#}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
						<label for="start_time">{#courseNewFormStartDate#}</label>
						<input id='start_time' name='start_time' value='' class="form-control" type='text' size ='16'>
						<p class="help-block">{#courseNewFormStartDateDesc#}</p>
					</div>

					<div class="form-group col-md-6">
						<label for="finish_time">{#courseNewFormEndDate#}</label>
						<input id='finish_time' name='finish_time' value='' class="form-control" type='text' size='16'>
						<p class="help-block">{#courseNewFormEndDateDesc#}</p>
					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-6">
						<label for="show_scoreboard">{#courseNewFormShowScoreboard#}</label>
						<select name='show_scoreboard' id='show_scoreboard' class="form-control">
							<option value='yes'>{#wordsYes#}</option>
							<option value='no'>{#wordsNo#}</option>
						</select>
						<p class="help-block">{#courseNewFormShowScoreboardDesc#}</p>
					</div>

					<div class="form-group col-md-6">
						<label for="description">{#courseNewFormDescription#}</label>
						<textarea id='description' name='description' cols="30" rows="10" class="form-control"></textarea>
					</div>

					{if $IS_UPDATE eq 1}
					<div class="form-group col-md-6">
						<label for="public">{#courseNewFormPublic#}</label>
						<select name='public' id='public' class="form-control">
							<option value='0' selected="selected">{#wordsNo#}</option>
							<option value='1'>{#wordsYes#}</option>
						</select>
						<p class="help-block">{#courseNewFormPublicDesc#}</p>
					</div>
					{/if}
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
<script type="text/javascript" src="/js/course.new.form.js?ver=734311"></script>

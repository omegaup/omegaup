{if !isset($IS_ASSIGNMENT_UPDATE)}
	{assign "IS_ASSIGNMENT_UPDATE" 0}
{/if}

<div class="panel">
	{if $IS_ASSIGNMENT_UPDATE != 1}
	<!-- <div class="panel-heading">
		<h3 class="panel-title">
			{#courseAssignmentNew#}
		</h3>
	</div> -->
	{/if}
	<div class="panel-body">
		<form class="new_course_assignment_form">
				<div class="row">
					<div class="form-group col-md-6">
						<label for="title">{#wordsTitle#}</label>
						<input id="title" name="title" value="" type="text" size="30" class="form-control">
					</div>

					<div class="form-group col-md-6">
						<label for="alias">{#courseNewFormShortTitle_alias_#}</label>
						<span data-toggle="tooltip" data-placement="top"
							  title="{#courseAssignmentNewFormShortTitle_alias_Desc#}"
							  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
						<input id="alias" name="alias" value="" type="text" class="form-control"
							{IF $IS_ASSIGNMENT_UPDATE eq 1} disabled="true" {/IF}>
					</div>
				</div>

				<div class="row">
					<div class="col-md-6">
						<div class="form-group col-md-6">
							<label>{#courseNewFormStartDate#}
								<span data-toggle="tooltip" data-placement="top"
										title="{#courseAssignmentNewFormStartDateDesc#}"
										class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
								<input name="start_time" value="" class="form-control" type="text" size ="16">
							</label>
						</div>

						<div class="form-group col-md-6">
							<label>{#courseNewFormEndDate#}
								<span data-toggle="tooltip" data-placement="top"
										title="{#courseAssignmentNewFormEndDateDesc#}"
										class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
								<input name="finish_time" value="" class="form-control" type="text" size="16">
							</label>
						</div>
					</div>

					<div class="form-group col-md-6">
						<label for="assignment_type">{#courseAssignmentNewFormType#}</label>
						<span data-toggle="tooltip" data-placement="top"
							  title="{#courseAssignmentNewFormTypeDesc#}"
							  class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
						<select name="assignment_type" id="assignment_type" class="form-control">
							<option value="homework">{#wordsHomework#}</option>
							<option value="test">{#wordsTest#}</option>
						</select>
					</div>
				</div>

				<div class="row">
					<div class="form-group container">
						<label for="description">{#courseNewFormDescription#}</label>
						<textarea id="description" name="description" cols="30" rows="5" class="form-control"></textarea>
					</div>
				</div>

				<div class="form-group">
				{if $IS_ASSIGNMENT_UPDATE eq 1}
					<button type="submit" class="btn btn-primary">{#courseAssignmentNewFormUpdate#}</button>
				{else}
					<button type="submit" class="btn btn-primary">{#courseAssignmentNewFormSchedule#}</button>
				{/if}
				</div>
		</form>
	</div>
</div>

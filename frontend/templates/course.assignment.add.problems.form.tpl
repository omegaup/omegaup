<div class="panel">
	<div class="panel-body">
		<form class="assignment-add-problem">
			<div class="row">
				<div class="form-group col-md-12">
					<label for="assignments-dropdown">{#wordsAssignments#}</label>
					<select name="assignments-list" id='assignments-list' class="form-control"></select>
				</div>
			</div>

			<div class="row">
				<div class="form-group col-md-12">
					<label for="problems-dropdown">{#wordsProblems#}</label>
					<input class="typeahead form-control" name="problems" id="problems-dropdown" autocomplete="off" />
					<p class="help-block">{#courseAddProblemsAssignmentsDesc#}</p>
				</div>
			</div>

			<div class="form-group">
				<button type='submit' class="btn btn-primary">{#courseAddProblemsAdd#}</button>
			</div>
		</form>
	</div>
</div>

<script type="text/javascript" src="/js/course.assignment.add.problems.form.js?ver=ac0d8b"></script>


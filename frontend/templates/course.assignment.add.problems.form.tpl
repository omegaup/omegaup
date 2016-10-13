<div class="panel">
	<div class="panel-body">
		<form class="assignment-add-problem">
			<div class="row">
				<div class="form-group col-md-6">
					<label for="assignments-dropdown">{#wordsAssignments#}</label>
					<select name="assignments-list" id='assignments-list' class="form-control"></select>
				</div>
			</div>
			<hr>
			<div class="row">
				<div class="col-md-4">
					<div class="form-group col-md-8">
						<label for="assignments-dropdown">{#wordsTopics#}</label>
						<select name="topics-list" id='topic-list' class="form-control"></select>
					</div>
					<div class="form-group col-md-8">
						<label for="assignments-dropdown">{#wordsLevels#}</label>
						<select name="level-list" id='level-list' class="form-control"></select>
					</div>
				</div>
				<div class="col-md-8">
					<div class="row">
						<div class="form-group col-md-8">
							<label for="problems-dropdown">{#wordsProblems#}</label>
							<textarea name="list-problems" id="list-problems" cols="70" rows="10"></textarea>
						</div>
					</div>
					<div class="row">
						<div class="form-group col-md-8">
							<label for="problems-dropdown">{#wordsProblems#}</label>
							<input class="typeahead form-control" name="problems" id="problems-dropdown" autocomplete="off" />
							<p class="help-block">{#courseAddProblemsAssignmentsDesc#}</p>
						</div>
					</div>
					<div class="form-group">
						<button type='submit' class="btn btn-primary">{#courseAddProblemsAdd#}</button>
					</div>
				</div>
			</div>
		</form>
	</div> <!-- panel-body -->
</div> <!-- panel -->

<script type="text/javascript" src="/js/course.assignment.add.problems.form.js?ver=ac0d8b"></script>


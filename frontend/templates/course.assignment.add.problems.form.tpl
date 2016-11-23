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
						<select name="topics-list" id='topic-list' class="form-control">
							<!-- TODO: How do we do this in general? -->
							<option value="binary-search">{#problemTopicBinarySearch#}</option>
							<option value="graph-theory">{#problemTopicGraphTheory#}</option>
							<option value="sorting">{#problemTopicSorting#}</option>
						</select>
					</div>
					<div class="form-group col-md-8">
						<label for="assignments-dropdown">{#wordsLevels#}</label>
						<select name="level-list" id='level-list' class="form-control">
							<option value="intro">{#problemLevelIntro#}</option>
							<option value="easy">{#problemLevelEasy#}</option>
							<option value="medium">{#problemLevelMedium#}</option>
							<option value="hard">{#problemLevelHard#}</option>
						</select>
					</div>
				</div>
				<div class="col-md-8">
					<div class="row">
						<div class="form-group col-md-8">
							<label for="problems-dropdown">{#wordsProblems#}</label>
							<select name="list-problems" id="list-problems" size="15"
									style="width:100%">
							</select>
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

<script type="text/javascript" src="{version_hash src="/js/course.assignment.add.problems.form.js"}"></script>


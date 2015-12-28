(function(){
	$(".navbar #nav-problems").addClass("active");

	function fillProblemsTable() {
		omegaup.getMyProblems(function(problems) {
			$('#problem-list .added').remove();
			for (var i = 0; i < problems.results.length; i++) {
				var row = $('#problem-list .problem-list-template')
					.clone()
					.removeClass('problem-list-template')
					.addClass('added');
				var problem = problems.results[i];
				$('input[type="checkbox"]', row).attr('id', problem.alias);
				$('.title', row)
					.attr('href', '/arena/problem/' + problem.alias + '/')
					.html(omegaup.escape(problem.title));
				if (problem.tags && problem.tags.length > 0) {
					var tags = $('.tag-list', row)
						.removeClass('hidden');
					for (var j = 0; j < problem.tags.length; j++) {
						tags.append($('<a class="tag"></a>')
							.attr('href', '/problem/?tag=' + omegaup.escape(problem.tags[j]))
							.html(omegaup.escape(problem.tags[j])));
					}
				}
				if (problem.public != '1') $('.private', row).removeClass('hidden');
				$('.edit', row).attr('href', '/problem/' + problem.alias + '/edit/');
				$('.stats', row).attr('href', '/problem/' + problem.alias + '/stats/');
				$('#problem-list').append(row);
			}
			$("#problem-list").removeClass("wait_for_ajax")
		});
	}
	fillProblemsTable();

	$("#bulk-make-public").click(function() {
		OmegaUp.ui.bulkOperation(
			function(alias, handleResponseCallback) {
				omegaup.updateProblem(alias, 1 /*public*/, handleResponseCallback);
			},
			function() {
				fillProblemsTable();
			}
		)}
	);

	$("#bulk-make-private").click(function() {
		OmegaUp.ui.bulkOperation(
			function(alias, handleError) {
				omegaup.updateProblem(alias, 0 /*public*/, handleResponseCallback);
			},
			function() {
				fillProblemsTable();
			}
		)}
	);
})();

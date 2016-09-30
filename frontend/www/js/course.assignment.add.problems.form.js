$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	var target = $(e.target).attr("href");

	// If add-problems tab is on focus
	if (target === "#add-problems") {
		var courseAlias = /\/course\/([^\/]+)\/edit\/?.*/.exec(window.location.pathname)[1];

		// Fill assignments
		omegaup.API.getCourseAssignments(courseAlias, function(data) {
			$('.assignment-add-problem #assignments-list').empty();

			$.each(data.assignments, function (index, item) {
				$('.assignment-add-problem #assignments-list').append($("<option/>", {
					value: item.alias,
					text: item.name
				}));
			});
		});

		// Plug problems type-ahead
		$('.assignment-add-problem #problems-dropdown').typeahead({
			minLength: 3,
			highlight: false,
			}, {
				source: function (query, cb) {
					omegaup.API.searchProblems(query, function (data) {
						cb(data.results);
					});
				},
				displayKey: 'alias',
				templates: {
					suggestion: function (elm) {
						return "<strong>" + elm.title + "</strong> (" + elm.alias + ")";
					}
				}
			}).on('typeahead:selected', function(item, val, text) {
				$('#problems-dropdown').val(val.alias);
			});
		}
});

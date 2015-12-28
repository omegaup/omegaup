$(".navbar #nav-contests").addClass("active");

var formData = $('#form-data');
var formName = formData.attr('data-name');
var formPage = formData.attr('data-page');
var formAlias = formData.attr('data-alias');

$(function() {
	if (formPage === "list") {
		function fillGroupsList() {
			omegaup.getGroups(function(groups) {
				var html = "";

				for (var i = 0; i < groups.groups.length; i++) {
					html += "<tr>"
						+ "<td><b><a href='/group/" + groups.groups[i].alias  + "/edit/#scoreboards'>" + omegaup.escape(groups.groups[i].name) + "</a></b></td>"
						+ '<td><a class="glyphicon glyphicon-edit" href="/group/' + groups.groups[i].alias  + '/edit#edit" title="{#wordsEdit#}"></a></td>'
						+ "</tr>";
				}

				$("#groups_list").removeClass("wait_for_ajax");
				$("#groups_list > table > tbody").empty().html(html);
			});
		}

		fillGroupsList();
	} else if (formPage === "new") {
		$('.new_group_form').submit(function() {
			omegaup.createGroup(
				$(".new_group_form #alias").val(),
				$(".new_group_form #title").val(),
				$(".new_group_form #description").val(),
				function(data) {
					if(data.status === "ok") {
						window.location.replace('/group/'+ $('.new_group_form #alias').val() + '/edit/#members');
					} else {
						OmegaUp.ui.error(data.error || 'error');
					}
				}
			);

			return false;
		});
	} else if (formPage === "edit") {
		var groupAlias = formAlias;

		// Sections UI actions
		if(window.location.hash){
			$('#sections').find('a[href="'+window.location.hash+'"]').tab('show');
		}

		$('#sections').on('click', 'a', function (e) {
			e.preventDefault();
			// add this line
			window.location.hash = $(this).attr('href');
			$(this).tab('show');
		});

		// Typehead
		refreshGroupMembers();
		$('#member-username').typeahead({
			minLength: 2,
			highlight: true,
		}, {
			source: omegaup.searchUsers,
			displayKey: 'label',
		}).on('typeahead:selected', function(item, val, text) {
			$('#member-username').val(val.label);
		});

		$('#add-member-form').submit(function() {
			var username = $('#member-username').val();

			omegaup.addUserToGroup(groupAlias, username, function(response) {
				if (response.status === "ok") {
					OmegaUp.ui.success("Member successfully added!");
					$('div.post.footer').show();

					refreshGroupMembers();
				} else {
					OmegaUp.ui.error(response.error || 'error');
				}
			});

			return false; // Prevent refresh
		});

		function refreshGroupMembers() {
			omegaup.getGroup(groupAlias, function(group){
				$('#group-members').empty();

				for (var i = 0; i < group.users.length; i++) {
					var user = group.users[i];
					$('#group-members').append(
						$('<tr></tr>')
							.append($('<td></td>').append(
								$('<a></a>')
									.attr('href', '/profile/' + user.userinfo.username + '/')
									.text(omegaup.escape(user.userinfo.username))
							))
							.append($('<td><button type="button" class="close">&times;</button></td>')
								.click((function(username) {
									return function(e) {
										omegaup.removeUserFromGroup(groupAlias, username, function(response) {
											if (response.status === "ok") {
												OmegaUp.ui.success("Member successfully removed!");
												$('div.post.footer').show();
												var tr = e.target.parentElement.parentElement;
												$(tr).remove();
											} else {
												OmegaUp.ui.error(response.error || 'error');
											}
										});
									};
								})(user.userinfo.username))
							)
					);
				}
			});
		}

		$('#add-scoreboard-form').submit(function() {
			var name = $('#title').val();
			var alias = $('#alias').val();
			var description = $('#description').val();

			omegaup.addScoreboardToGroup(groupAlias, alias, name, description, function(response) {
				if (response.status === "ok") {
					OmegaUp.ui.success("Scoreboard successfully added!");
					$('div.post.footer').show();

					refreshGroupScoreboards();
				} else {
					OmegaUp.ui.error(response.error || 'error');
				}
			});

			return false; // Prevent refresh
		});

		function refreshGroupScoreboards() {
			omegaup.getGroup(groupAlias, function(group){
				$('#group-scoreboards').empty();

				for (var i = 0; i < group.scoreboards.length; i++) {
					var scoreboard = group.scoreboards[i];
					$('#group-scoreboards').append(
						$('<tr></tr>')
							.append($('<td></td>').append(
								$('<a></a>')
									.attr('href', '/group/' + groupAlias + '/scoreboard/' + scoreboard.alias + '/')
									.text(omegaup.escape(scoreboard.name))
							))
							.append($('<td><a class="glyphicon glyphicon-edit" href="/group/' + groupAlias + '/scoreboard/' + scoreboard.alias  + '/edit/" title="Edit"></a></td>'))
					);
				}
			});
		}

		refreshGroupScoreboards();
	}
});


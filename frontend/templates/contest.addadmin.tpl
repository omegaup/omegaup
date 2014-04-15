{assign var="isAddAdmin" value=true}
{assign var="htmlTitle" value="{#omegaupTitleContestAddAdmin#}"}
{include file='contest.adduser.tpl'}

<script>
	$('#add-user-form').submit(function() {
		contestAlias = $('select#contests').val();
		username = $("#user").val();
		omegaup.addAdminToContest(contestAlias, username, function(response) {
			if (response.status == "ok") {
				OmegaUp.ui.success("Admin successfully added!");
				$('div.post.footer').show();								
			} else {
				OmegaUp.ui.error(response.error || 'error');
			}
		});
		return false; // Prevent refresh
	});
</script>
{include file='head.tpl' navbarSection='users' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProfile#}" inline}

{if !isset($STATUS_ERROR)}

<div class="row" id="inner-content">
	<div class="col-md-2 no-right-padding" id="userbox">
		<div class="panel panel-default" id="userbox-inner">
			<div class="panel-body">
				<div class="thumbnail bottom-margin"> <img src="{$profile.gravatar_92}"/></div>
				{if isset($profile.email)}
				<div id="profile-edit"><a href="/profile/edit/" class="btn btn-default">{#profileEdit#}</a></div>
				{/if}
			</div>
		</div>
	</div>

	{block name="content"}
	{/block}

</div>
<div id="username" style="display:none" data-username="{$profile.username|replace:"\\":""}"></div>

<script src="{version_hash src="/third_party/js/iso-3166-2.js/iso3166.min.js"}"></script>

{/if}

{include file='footer.tpl' inline}

<!DOCTYPE html>
<html lang="{#locale#}" {if isset($headerPayload) && $headerPayload.bootstrap4} class="h-100" {/if}>
	<head data-locale="{#locale#}">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
{if !is_null($smarty.const.NEW_RELIC_SCRIPT)}
		{$smarty.const.NEW_RELIC_SCRIPT}
{/if}
{if isset($inArena) && $inArena}
		{assign var='navbarSection' value='arena'}
{else}
		<meta name="google-signin-client_id" content="{$GOOGLECLIENTID}">
{/if}
		<script type="text/javascript" src="{version_hash src="/js/error_handler.js"}"></script>
		<title>{if isset($htmlTitle)}{$htmlTitle} &ndash; {/if}omegaUp</title>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery-3.5.1.min.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/jquery_error_handler.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js" defer}" defer></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js" defer}"></script>

		{js_include entrypoint="omegaup" runtime}

{if isset($jsfile) && !is_null($jsfile)}
		<script type="text/javascript" src="{$jsfile}" defer></script>
{/if}
		<script type="text/javascript" src="{version_hash src="/js/head.sugar_locale.js"}" defer></script>
{if isset($headerPayload) && $headerPayload.bootstrap4}
		<link rel="stylesheet" href="/third_party/bootstrap-4.5.0/css/bootstrap.min.css"/>
		<script src="/third_party/bootstrap-4.5.0/js/bootstrap.bundle.min.js"></script>
{else}
		<!-- Bootstrap -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/third_party/bootstrap-3.4.1/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="{version_hash src="/third_party/bootstrap-3.4.1/js/bootstrap.min.js"}" defer></script>
{/if}

{if isset($inArena) && $inArena}
    <link rel="stylesheet" type="text/css" href="{version_hash src="/css/arena.css"}" />
{elseif !isset($headerPayload) || !$headerPayload.bootstrap4}
		<link rel="stylesheet" type="text/css" href="{version_hash src="/css/style.css"}">
		<!-- Bootstrap table plugin from https://github.com/wenzhixin/bootstrap-table/releases -->
		<script src="{version_hash src="/third_party/js/bootstrap-table.min.js"}" defer></script>
		<link rel="stylesheet" href="/third_party/css/bootstrap-table.min.css">
{/if}
{if !isset($headerPayload) || !$headerPayload.bootstrap4}
		<link rel="stylesheet" type="text/css" href="{version_hash src="/css/common.css"}" />
		<link rel="stylesheet" type="text/css" href="{version_hash src="/third_party/wenk/demo/wenk.min.css"}" />
{/if}
		<link rel="stylesheet" type="text/css" href="{version_hash src="/css/dist/omegaup_styles.css"}">
		<link rel="shortcut icon" href="/favicon.ico" />

{if !empty($ENABLED_EXPERIMENTS)}
		<script type="text/plain" id="omegaup-enabled-experiments">{','|implode:$ENABLED_EXPERIMENTS}</script>
{/if}
{if isset($recaptchaFile)}
		<script type="text/javascript" src="{$recaptchaFile}"></script>
{/if}
	</head>
	<body
		{if isset($bodyid) and $bodyid} id="{$bodyid|escape}"{/if}
		class="{if isset($headerPayload) && $headerPayload.bootstrap4} d-flex flex-column h-100 pt-5{/if}{if $smarty.const.OMEGAUP_LOCKDOWN} lockdown{/if}"
	>
{if isset($inArena) && $inArena}
		<!-- Generated from http://ajaxload.info/ -->
		{if !isset($bodyid) or $bodyid != 'only-problem'}
		<div id="loading" style="text-align: center; position: fixed; width: 100%; margin-top: -8px; top: 50%;"><img src="/media/loading.gif" alt="loading" /></div>
		{/if}
{/if}
{if isset($headerPayload) && $headerPayload.bootstrap4}
	<script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
	<div id="common-navbar"></div>
	{js_include entrypoint="common_navbar"}
	<main role="main">
		{if (!isset($inArena) || !$inArena) && isset($ERROR_MESSAGE)}
			<div class="alert alert-danger">
				{$ERROR_MESSAGE}
			</div>
		{/if}
		{include file='status.tpl' inline}
	</main>
{else}
	<div id="root">
	<div id="common-navbar"></div>
	{if isset($headerPayload)}
		<script type="text/json" id="header-payload">{$headerPayload|json_encode}</script>
	{else}
		<script type="text/json" id="header-payload">{[]}</script>
	{/if}
	{if (!isset($inArena) || !$inArena)}
	  	{js_include entrypoint="common_navbar"}
		<div id="content">
		{if isset($ERROR_MESSAGE)}
		<div class="alert alert-danger">
			{$ERROR_MESSAGE}
		</div>
		{/if}
	{/if}
	{include file='status.tpl' inline}
{/if}

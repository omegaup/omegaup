<!DOCTYPE html>
<html lang="{#locale#}" {if isset($headerPayload) && $headerPayload.bootstrap4} class="h-100" {/if}>
	<head data-locale="{#locale#}">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
{if !is_null($smarty.const.NEW_RELIC_SCRIPT)}
		{$smarty.const.NEW_RELIC_SCRIPT}
{/if}
{if isset($inArena) && $inArena}
		{assign var='LOAD_MATHJAX' value='true'}
		{assign var='navbarSection' value='arena'}
{else}
		<meta name="google-signin-client_id" content="{$GOOGLECLIENTID}">
{/if}
		<script type="text/javascript" src="{version_hash src="/js/error_handler.js"}"></script>
		<title>{if isset($htmlTitle)}{$htmlTitle} &ndash; {/if}omegaUp</title>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery-3.4.1.min.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/jquery_error_handler.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js" defer}" defer></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js" defer}"></script>

		{js_include entrypoint="omegaup" runtime}
		<script type="text/javascript" src="{version_hash src="/js/require_helper.js"}"></script>
{if isset($inArena) && $inArena}
		{js_include entrypoint="arena"}
{/if}
{if (isset($inArena) && $inArena) || (isset($loadMarkdown) && $loadMarkdown)}
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery.tableSort.js"}" defer></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js" defer}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Sanitizer.js" defer}"></script>
{/if}

{if isset($jsfile)}
		<script type="text/javascript" src="{$jsfile}" defer></script>
{/if}
{if isset($LOAD_MATHJAX) && $LOAD_MATHJAX}
		<script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}" defer></script>
		<script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML" defer></script>
{/if}
		<script type="text/javascript" src="{version_hash src="/js/langtools.js"}" defer></script>
		<script type="text/javascript" src="{version_hash src="/js/head.sugar_locale.js"}" defer></script>
{if isset($headerPayload) && $headerPayload.bootstrap4}
		<link rel="stylesheet" href="/third_party/bootstrap-4.4.1/css/bootstrap.min.css"/>
		<script src="/third_party/bootstrap-4.4.1/js/bootstrap.bundle.min.js"></script>
{else}
		<!-- Bootstrap -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/third_party/bootstrap-3.4.1/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="{version_hash src="/third_party/bootstrap-3.4.1/js/bootstrap.min.js"}" defer></script>
{/if}

{if isset($inArena) && $inArena}
		<link rel="stylesheet" type="text/css" href="{version_hash src="/ux/arena.css"}" />
{else}
		<link rel="stylesheet" type="text/css" href="{version_hash src="/css/style.css"}">
		<!-- Bootstrap table plugin from https://github.com/wenzhixin/bootstrap-table/releases -->
		<script src="{version_hash src="/third_party/js/bootstrap-table.min.js"}" defer></script>
		<link rel="stylesheet" href="/third_party/css/bootstrap-table.min.css">
{/if}
		<link rel="stylesheet" type="text/css" href="{version_hash src="/css/common.css"}" />
		<link rel="stylesheet" type="text/css" href="{version_hash src="/css/dist/omegaup_styles.css"}">
		<link rel="stylesheet" type="text/css" href="{version_hash src="/third_party/wenk/demo/wenk.min.css"}" />
		<link rel="shortcut icon" href="/favicon.ico" />

{if isset($LOAD_PAGEDOWN) && $LOAD_PAGEDOWN}
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js"}" defer></script>
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Sanitizer.js"}" defer></script>
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Editor.js"}" defer></script>
	<link rel="stylesheet" type="text/css" href="/css/markdown-editor-widgets.css" />
{/if}
{if !empty($ENABLED_EXPERIMENTS)}
		<script type="text/plain" id="omegaup-enabled-experiments">{','|implode:$ENABLED_EXPERIMENTS}</script>
{/if}
{if isset($recaptchaFile)}
		<script type="text/javascript" src="{$recaptchaFile}"></script>
{/if}
	</head>
	<body
		{if isset($bodyid) and $bodyid} id="{$bodyid|escape}"{/if}
		class="{if isset($headerPayload) && $headerPayload.bootstrap4} d-flex flex-column h-100 pt-4{/if} {if $smarty.const.OMEGAUP_LOCKDOWN} lockdown{/if}"
	>
{if isset($inArena) && $inArena}
		<!-- Generated from http://ajaxload.info/ -->
		{if !isset($bodyid) or $bodyid != 'only-problem'}
		<div id="loading" style="text-align: center; position: fixed; width: 100%; margin-top: -8px; top: 50%;"><img src="/ux/loading.gif" alt="loading" /></div>
		{/if}
{/if}
{if isset($headerPayload) && $headerPayload.bootstrap4}
	{include file='common.navbar.tpl' headerPayload=$headerPayload inline}
	<main role="main">
		{if !isset($inArena) || !$inArena}
			{include file='mainmenu.tpl' inline}
		{/if}
		{include file='status.tpl' inline}
	</main>
{else}
	<div id="root">
	{if isset($headerPayload)}
		{include file='common.navbar.tpl' headerPayload=$headerPayload inline}
	{else}
		{include file='common.navbar.tpl' headerPayload=[] inline}
	{/if}
	{if !isset($inArena) || !$inArena}
	{include file='mainmenu.tpl' inline}
	{/if}
	{include file='status.tpl' inline}
{/if}

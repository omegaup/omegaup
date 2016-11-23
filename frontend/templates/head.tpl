<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head data-locale="{#locale#}">
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="google-signin-client_id" content="{$GOOGLECLIENTID}">
		<title>{if isset($htmlTitle)}{$htmlTitle} &ndash; {/if}omegaUp</title>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery-1.10.2.min.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/knockout-4.3.0.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/knockout-secure-binding.min.js"}"></script>

		<script type="text/javascript" src="{version_hash src="/js/omegaup/omegaup.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/api.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/ui.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/lang.#locale#.js"}"></script>

{if isset($jsfile)}
		<script type="text/javascript" src="{$jsfile}"></script>
{/if}
{if isset($LOAD_MATHJAX) && $LOAD_MATHJAX}
		<script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}"></script>
		<script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
{/if}
		<link rel="stylesheet" href="/third_party/css/reset.css" />
		<script type="text/javascript" src="{version_hash src="/js/omegaup-graph.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/langtools.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/head.sugar_locale.js"}"></script>

		<!-- Bootstrap from CDN -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/third_party/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="{version_hash src="/third_party/js/bootstrap.min.js"}"></script>
		<!-- Bootstrap table plugin from https://github.com/wenzhixin/bootstrap-table/releases -->
		<script src="{version_hash src="/third_party/js/bootstrap-table.min.js"}"></script>
		<link rel="stylesheet" href="/third_party/css/bootstrap-table.min.css">
		<!-- Bootstrap select plugin from https://github.com/silviomoreto/bootstrap-select -->
		<link rel="stylesheet" href="/third_party/css/bootstrap-select.min.css">
		<script type="text/javascript" src="{version_hash src="/third_party/js/bootstrap-select.min.js"}"></script>
		<!-- Bootstrap datepicker plugin from http://www.eyecon.ro/bootstrap-datepicker/ -->
		<link rel="stylesheet" href="/third_party/css/bootstrap-datepicker.css">
		<script type="text/javascript" src="{version_hash src="/third_party/js/bootstrap-datepicker.js"}"></script>
		<!-- typeahead plugin from https://github.com/twitter/typeahead.js -->
		<script type="text/javascript" src="{version_hash src="/third_party/js/typeahead.jquery.js"}"></script>
		<!-- Bootstrap datetimepicker plugin from http://www.malot.fr/bootstrap-datetimepicker/demo.php -->
		<link rel="stylesheet" href="/third_party/css/bootstrap-datetimepicker.css">
		<script type="text/javascript" src="{version_hash src="/third_party/js/bootstrap-datetimepicker.min.js"}"></script>

		<link rel="stylesheet" type="text/css" href="/css/common.css" />
		<link rel="stylesheet" type="text/css" href="/css/style.css">
		<link rel="shortcut icon" href="/favicon.ico" />

{if isset($LOAD_PAGEDOWN) && $LOAD_PAGEDOWN}
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js"}"></script>
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Sanitizer.js"}"></script>
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Editor.js"}"></script>
	<link rel="stylesheet" type="text/css" href="/third_party/js/pagedown/demo/browser/demo.css" />
{/if}
{if !empty($ENABLED_EXPERIMENTS)}
		<script type="text/plain" id="omegaup-enabled-experiments">{','|implode:$ENABLED_EXPERIMENTS}</script>
{/if}
	</head>
	<body{if isset($bodyid) and $bodyid} id="{$bodyid|escape}"{/if}{if $smarty.const.OMEGAUP_LOCKDOWN} class="lockdown"{/if}>
		<div id="wrapper">
{include file='common.navbar.tpl'}

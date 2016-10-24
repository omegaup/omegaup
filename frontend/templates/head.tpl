<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head data-locale="{#locale#}">

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="google-signin-client_id" content="{$GOOGLECLIENTID}">

		<title>{$htmlTitle} &ndash; omegaUp</title>

		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery.js"}"></script>

		<script type="text/javascript" src="{version_hash src="/js/omegaup/omegaup.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/api.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/ui.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/lang.#locale#.js"}"></script>

		<script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup-graph.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/langtools.js"}"></script>

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

		<!-- from arena -->
		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="/css/style.css">
		<link rel="stylesheet" type="text/css" href="/css/common.css">

{if isset($LOAD_MATHJAX) && $LOAD_MATHJAX}
	<script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}"></script>
	<script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
{/if}
{if isset($LOAD_PAGEDOWN) && $LOAD_PAGEDOWN}
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js"}"></script>
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Sanitizer.js"}"></script>
	<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Editor.js"}"></script>
	<link rel="stylesheet" type="text/css" href="/third_party/js/pagedown/demo/browser/demo.css" />
{/if}
		<script type="text/javascript" src="{version_hash src="/js/head.sugar_locale.js"}"></script>
		{if isset($jsfile)}
			<script type="text/javascript" src="{$jsfile}"></script>
		{/if}
	</head>
	<body>

		<div id="wrapper">
{if isset($navbarSection)}
{include file='common.navbar.tpl' navbarSection=$navbarSection}
{else}
{include file='common.navbar.tpl'}
{/if}

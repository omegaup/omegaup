<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://www.facebook.com/2008/fbml">
	<head data-locale="{#locale#}">

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="google-signin-client_id" content="{$GOOGLECLIENTID}">

		<title>{$htmlTitle} &ndash; omegaUp</title>

		<script type="text/javascript" src="/js/jquery.js?ver=198b3f"></script>

		<script type="text/javascript" src="/js/omegaup.js?ver=d931a6"></script>
		<script type="text/javascript" src="/js/lang.{#locale#}.js?ver=6f8b8b,e2245f,9c2261,8fd3f0"></script>

		<script type="text/javascript" src="/js/sugar.js?ver=171bac"></script>
		<script type="text/javascript" src="/js/highstock.js?ver=6e7575"></script>
		<script type="text/javascript" src="/js/omegaup-graph.js?ver=12891c"></script>
		<script type="text/javascript" src="/js/langtools.js?ver=adeec4"></script>

		<!-- Bootstrap from CDN -->
		<!-- Latest compiled and minified CSS -->

		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<!-- Optional theme -->

		<link rel="stylesheet" href="/css/bootstrap-theme.min.css">
		<!-- Latest compiled and minified JavaScript -->

		<script src="/js/bootstrap.min.js?ver=176563"></script>

		<!-- Bootstrap table plugin from https://github.com/wenzhixin/bootstrap-table/releases -->
		<script src="/js/bootstrap-table.min.js?ver=711245"></script>
		<link rel="stylesheet" href="/css/bootstrap-table.min.css">

		<!-- Bootstrap select plugin from https://github.com/silviomoreto/bootstrap-select -->
		<link rel="stylesheet" href="/css/bootstrap-select.min.css">
		<script type="text/javascript" src="/js/bootstrap-select.min.js?ver=cf5db5"></script>
		<!-- Bootstrap datepicker plugin from http://www.eyecon.ro/bootstrap-datepicker/ -->
		<link rel="stylesheet" href="/css/bootstrap-datepicker.css">
		<script type="text/javascript" src="/js/bootstrap-datepicker.js?ver=bf3a56"></script>
		<!-- typeahead plugin from https://github.com/twitter/typeahead.js -->
		<script type="text/javascript" src="/js/typeahead.jquery.js?ver=2e4977"></script>
		<!-- Bootstrap datetimepicker plugin from http://www.malot.fr/bootstrap-datetimepicker/demo.php -->
		<link rel="stylesheet" href="/css/bootstrap-datetimepicker.css">
		<script type="text/javascript" src="/js/bootstrap-datetimepicker.min.js?ver=a0cafb"></script>

		<!-- from arena -->
		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="stylesheet" type="text/css" href="/css/style.css">
		<link rel="stylesheet" type="text/css" href="/css/common.css">

{if isset($LOAD_MATHJAX) && $LOAD_MATHJAX}
	<script type="text/javascript" src="/js/mathjax-config.js?ver=37494e"></script>
	<script type="text/javascript" src="/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
{/if}
{if isset($LOAD_PAGEDOWN) && $LOAD_PAGEDOWN}
	<script type="text/javascript" src="/js/pagedown/Markdown.Converter.js?ver=bbca63"></script>
	<script type="text/javascript" src="/js/pagedown/Markdown.Sanitizer.js?ver=25306e"></script>
	<script type="text/javascript" src="/js/pagedown/Markdown.Editor.js?ver=1bf05c"></script>
	<link rel="stylesheet" type="text/css" href="/js/pagedown/demo/browser/demo.css" />
{/if}
		<script type="text/javascript" src="/js/head.sugar_locale.js?ver=0cb37f"></script>
	</head>
	<body>

		<div id="wrapper">
{if isset($navbarSection)}
{include file='common.navbar.tpl' navbarSection=$navbarSection}
{else}
{include file='common.navbar.tpl'}
{/if}

<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>{if isset($title)}{$title|escape} &ndash; {/if}omegaUp</title>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery-1.10.2.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery.ba-hashchange.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery.gritter.min.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/jquery.tableSort.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/highstock.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/sugar.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/knockout-4.3.0.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/third_party/js/knockout-secure-binding.min.js"}"></script>

		<script type="text/javascript" src="{version_hash src="/js/omegaup/omegaup.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/api.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/ui.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/lang.#locale#.js"}"></script>
		<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/arena.js"}"></script>

		{if isset($jsfile)}
		<script type="text/javascript" src="{$jsfile}"></script>
		{/if}
		<script type="text/javascript" src="{version_hash src="/js/mathjax-config.js"}"></script>
		<script type="text/javascript" src="/third_party/js/mathjax/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<link rel="stylesheet" href="/third_party/css/reset.css" />

		<!-- Bootstrap from CDN -->
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="/third_party/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="{version_hash src="/third_party/js/bootstrap.min.js"}"></script>
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

		<link rel="stylesheet" href="/third_party/css/jquery.gritter.css" />
		<link rel="stylesheet" href="/css/common.css" />
		<link rel="stylesheet" href="{version_hash src="/ux/arena.css"}" />
		<link rel="shortcut icon" href="/favicon.ico" />
{if !empty($ENABLED_EXPERIMENTS)}
		<script type="text/plain" id="omegaup-enabled-experiments">{','|implode:$ENABLED_EXPERIMENTS}</script>
{/if}
	</head>
	<body{if isset($bodyid) and $bodyid} id="{$bodyid|escape}"{/if}{if $smarty.const.OMEGAUP_LOCKDOWN} class="lockdown"{/if}>

		<!-- Generated from http://ajaxload.info/ -->
		{if !isset($bodyid) or $bodyid != 'only-problem'}
		<div id="loading" style="text-align: center; position: fixed; width: 100%; margin-top: -8px; top: 50%;"><img src="/ux/loading.gif" alt="loading" /></div>
		{/if}
		<div id="root">
{include file='common.navbar.tpl' navbarSection='arena'}
{include file='status.tpl'}

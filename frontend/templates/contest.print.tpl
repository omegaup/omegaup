<!doctype html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>{$smarty.get.contest_alias}</title>
{literal}
		<script type="text/javascript" src="https://c328740.ssl.cf1.rackcdn.com/mathjax/latest/MathJax.js?config=TeX-AMS-MML_HTMLorMML"></script>
		<script type="text/x-mathjax-config">MathJax.Hub.Config({tex2jax: {inlineMath: [['$','$'], ['\\(','\\)']]}});</script>
{/literal}

<link rel="stylesheet" href="/css/reset.css" />
<link rel="stylesheet" href="/css/common.css" />
<link rel="stylesheet" href="/ux/arena.css?t=2" />
<link rel="stylesheet" href="/css/report.css" />
</head>
<body id="report">
{foreach name=outer item=problem from=$problems}
	<div id="title">
		<h1 class="contest-title">{$problem.title}</h1>
	</div>
	<div class="statement">
		{$problem.statement}
	</div>
	<hr/>
	<div class="page-break"></div>
{/foreach}
</body>
</html>

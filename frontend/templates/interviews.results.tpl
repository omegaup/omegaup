{include file='head.tpl' htmlTitle="{#interviewList#}" inline}

<div class="page-header">
	<h1><span><img src="/media/wait.gif" /></span><small></small></h1>
	<h3><small></small></h3>
</div>

<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title">{#omegaupTitleProfile#}</h3>
	</div>

{include file='profile.basicinfo.tpl' inline}

	<div class="panel-body">
	</div>
</div>

<div class="panel panel-primary">
  <table class="runs"></table>
</div>

<script type="text/javascript" src="{version_hash src="/js/omegaup/arena/arena.js"}" defer></script>
<script type="text/javascript" src="{version_hash src="/js/interviews.results.js"}" defer></script>
{include file='footer.tpl' inline}


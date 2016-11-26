{include file='redirect.tpl'}
{include file='head.tpl' htmlTitle='{#omegaupTitleMyProblemsList#}'}

{if $PRIVATE_PROBLEMS_ALERT eq 1}
	<div class="alert alert-info">
		<span class="message">
			{#messageMakeYourProblemsPublic#}
		</span>
	</div>
{/if}

<div class="wait_for_ajax panel panel-default no-bottom-margin">
  <div class="panel-heading">
    <h3 class="panel-title">{#myproblemsListMyProblems#}</h3>
  </div>
  <div class="panel-body">
    <div class="checkbox btn-group">
      <label>
        <input type="checkbox" id="show-admin-problems" />
        {#problemListShowAdminProblems#}
      </label>
    </div>
    <div class="btn-group">
      <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
        {#forSelectedItems#}<span class="caret"></span>
      </button>
      <ul class="dropdown-menu" role="menu">
        <li><a id="bulk-make-public">{#makePublic#}</a></li>
        <li><a id="bulk-make-private">{#makePrivate#}</a></li>
      </ul>
    </div>
  </div>
  <table class="table" id="problem-list">
    <thead>
      <tr>
        <th></th>
        <th>{#wordsTitle#}</th>
        <th>{#wordsEdit#}</th>
        <th>{#wordsStatistics#}</th>
      </tr>
    </thead>
    <tbody class="problem-list-template">
      <tr>
        <td>
          <input type="checkbox"></td>
        <td>
          <a class="title"></a> <span class="glyphicon glyphicon-eye-close private hidden" title="{#wordsPrivate#}"></span>
          <div class="tag-list hidden"></div>
        </td>
        <td>
          <a class="glyphicon glyphicon-edit edit"></a>
        </td>
        <td>
          <a class="glyphicon glyphicon-stats stats"></a>
        </td>
      </tr>
    </tbody>
  </table>
</div>
<script type="text/javascript" src="{version_hash src="/js/problem.mine.js"}"></script>
{include file='footer.tpl'}

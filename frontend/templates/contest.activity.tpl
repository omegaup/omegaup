{include file='redirect.tpl'}
{include file='head.tpl' jsfile={version_hash src="/js/contest.activity.js"} htmlTitle="{#contestActivityReport#}"}

<div class="post">
  <div class="copy">
    <h1><a href="/arena/{$smarty.get.contest|escape}/">
        {$smarty.get.contest}</a> &mdash; {#contestActivityReport#}</h1>
    <p>{#contestActivityReportSummary#}</p>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" class="active">
        <a href="#report" aria-controls="report" role="tab"
           data-toggle="tab">{#contestActivityReportReport#}</a>
      </li>
      <li role="presentation">
        <a href="#users" aria-controls="users" role="tab"
           data-toggle="tab">{#contestActivityReportUsers#}</a>
      </li>
      <li role="presentation">
        <a href="#origins" aria-controls="origins" role="tab"
           data-toggle="tab">{#contestActivityReportOrigins#}</a>
      </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="report">
        <table class="table">
          <thead>
            <tr>
              <th>{#profileUsername#}</th>
              <th>{#wordsTime#}</th>
              <th>{#contestActivityReportOrigin#}</th>
              <th colspan="2">{#contestActivityReportEvent#}</th>
            </tr>
          </thead>
          <tbody data-bind="foreach: events">
            <tr>
              <td><a data-bind="text: username, attr: { href: profile_url }"></a></td>
              <td data-bind="text: time"></td>
              <td data-bind="text: ip"></td>
              <td data-bind="text: event.name"></td>
              <td><a data-bind="text: event.problem, attr: { href: event.problem_url }"></a></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div role="tabpanel" class="tab-pane" id="users">
        <p data-bind="visible: users.length == 0"
            >{#contestActivityReportNoDuplicatesForUsers#}</p>
        <table class="table" data-bind="visible: users.length > 0">
          <caption>{#contestActivityReportDuplicatesForUsersDescription#}</caption>
          <thead>
            <tr>
              <th>{#profileUsername#}</th>
              <th>{#contestActivityReportOrigin#}</th>
            </tr>
          </thead>
          <tbody data-bind="foreach: users">
            <tr>
              <td><a data-bind="text: username, attr: { href: profile_url }"
                  ></a></td>
              <td data-bind="text: ips"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div role="tabpanel" class="tab-pane" id="origins">
        <p data-bind="visible: origins.length == 0"
            >{#contestActivityReportNoDuplicatesForOrigins#}</p>
        <table class="table" data-bind="visible: origins.length > 0">
          <caption>{#contestActivityReportDuplicatesForOriginsDescription#}</caption>
          <thead>
            <tr>
              <th>{#contestActivityReportOrigin#}</th>
              <th>{#profileUsername#}</th>
            </tr>
          </thead>
          <tbody data-bind="foreach: origins">
            <tr>
              <td data-bind="text: origin"></td>
              <td>
                <span data-bind="foreach: usernames">
                  <a data-bind="text: username, attr: { href: profile_url }"
                     ></a>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

{include file='footer.tpl'}

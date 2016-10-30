{include file='redirect.tpl'}
{assign var="htmlTitle" value="{#contestActivityReport#}"}
{include file='head.tpl' jsfile={version_hash src="/js/contest.activity.js"}}
{include file='mainmenu.tpl'}
{include file='status.tpl'}

<script type="text/javascript" src="{version_hash src="/third_party/js/knockout-4.3.0.js"}"></script>
<script type="text/javascript" src="{version_hash src="/third_party/js/knockout-secure-binding.min.js"}"></script>

<div class="post">
  <div class="copy">
    <h1>{#contestActivityReport#}</h1>
    <table class="table" id="report-table">
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
</div>

{include file='footer.tpl'}

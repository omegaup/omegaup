{include file='redirect.tpl' inline}
{include file='head.tpl' navbarSection='problems' headerPayload=$headerPayload htmlTitle="{#omegaupTitleProblemEdit#}" inline}

<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Converter.js"}" defer></script>
<script type="text/javascript" src="{version_hash src="/third_party/js/pagedown/Markdown.Editor.js"}" defer></script>
<link rel="stylesheet" type="text/css" href="/css/markdown-editor-widgets.css" />

{js_include entrypoint="problem_edit"}

<div class="alert alert-warning slow-warning" style="display: none;">{#problemEditSlowWarning#}</div>

<div class="page-header">
  <h1><span>{#frontPageLoading#}</span> <small></small></h1>
  <p><a href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup">{#navHelp#}</a></p>
</div>

<ul class="nav nav-tabs nav-justified" id="sections">
  <li class="active"><a href="#edit" data-toggle="tab">{#problemEditEditProblem#}</a></li>
  <li><a href="#markdown" data-toggle="tab">{#problemEditEditMarkdown#}</a></li>
  <li><a href="#version" data-toggle="tab">{#problemEditChooseVersion#}</a></li>
  <li><a href="#solution" data-toggle="tab">{#problemEditSolution#}</a></li>
  <li><a href="#admins" data-toggle="tab">{#problemEditAddAdmin#}</a></li>
  <li><a href="#tags" data-toggle="tab">{#problemEditAddTags#}</a></li>
  <li><a href="#download" data-toggle="tab">{#wordsDownload#}</a></li>
  <li><a href="#delete" data-toggle="tab">{#wordsDelete#}</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane active" id="edit">
    <div id="problem-edit"></div>
    <script type="text/json" id="problem-edit-payload">{$problemEditPayload|json_encode}</script>
    {js_include entrypoint="problem_edit_form"}
  </div>

  <div class="tab-pane" id="markdown">
    <div></div>
    <script type="text/json" id="problem-markdown-payload">{$problemMarkdownPayload|json_encode}</script>
    <input type="hidden" name="problem_alias" id="problem-alias" value="{$smarty.get.problem}" />
  </div>

  <div class="tab-pane" id="admins">
    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form" id="add-admin-form">
          <div class="form-group">
            <label for="username-admin">{#wordsAdmin#}</label>
            <input id="username-admin" name="username" value="" type="text" size="20" class="form-control" autocomplete="off" />
          </div>

          <div class="form-group">
            <div class="col-xs-5 col-sm-3 col-md-3 action-container">
              <button class="btn btn-primary" type="submit">{#wordsAddAdmin#}</button>
            </div>
            <div class="col-xs-7 col-sm-9 col-md-9 toggle-container">
              <input type="checkbox" name="toggle-site-admins" id="toggle-site-admins">
              <label for="toggle-site-admins">{#wordsShowSiteAdmins#}</label>
            </div>
          </div>
        </form>
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>{#contestEditRegisteredAdminUsername#}</th>
            <th>{#contestEditRegisteredAdminRole#}</th>
            <th>{#contestEditRegisteredAdminDelete#}</th>
          </tr>
        </thead>
        <tbody id="problem-admins"></tbody>
      </table>
    </div>

    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form" id="add-group-admin-form">
          <div class="form-group">
            <label for="groupalias-admin">{#wordsGroupAdmin#}</label>
            <input id="groupalias-admin" name="name" value="" type="text" size="20" class="form-control" autocomplete="off" />
          </div>

          <button class="btn btn-primary" type='submit'>{#contestAddgroupAddGroup#}</button>
        </form>
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>{#contestEditRegisteredGroupAdminName#}</th>
            <th>{#contestEditRegisteredAdminRole#}</th>
            <th>{#contestEditRegisteredAdminDelete#}</th>
          </tr>
        </thead>
        <tbody id="problem-group-admins"></tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane" id="version">
    <div class="panel panel-default">
      <div></div>
    </div>
  </div>

  <div class="tab-pane" id="solution">
    <div id="solution-edit"></div>
  </div>

  <div class="tab-pane" id="tags">
    <div id="problem-tags"></div>
    <script type="text/json" id="problem-tags-payload">{$problemTagsPayload|json_encode}</script>
    {js_include entrypoint="problem_tags"}
  </div>

  <div class="tab-pane" id="download">
    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form">
          <div class="form-group">
            <button class="btn btn-primary" type='submit'>{#wordsDownload#}</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="tab-pane" id="delete">
    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form">
          <div class="form-group">
            <div class="alert alert-danger">
              <h4 class="alert-heading">{#wordsDangerZone#}</h4>
              <hr>
              {#wordsDangerZoneDesc#}
              <br><br>
              <button class="btn btn-danger" type='submit'>{#wordsDelete#}</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{include file='footer.tpl' inline}

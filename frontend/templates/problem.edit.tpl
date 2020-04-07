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
    {include file='problem.edit.form.tpl' inline}
  </div>

  <div class="tab-pane" id="markdown">
    <div class="panel panel-primary">
      <form class="panel-body form" method="post" action="{$smarty.server.REQUEST_URI}" enctype="multipart/form-data">
        <input type="hidden" name="problem_alias" id="problem-alias" value="{$smarty.get.problem}" />
        <input type="hidden" name="request" value="markdown" />
        <div class="row">
          <label for="statement-language">{#statementLanguage#}</label>
          <select name="statement-language" id="statement-language">
            <option value="es">{#statementLanguageEs#}</option>
            <option value="en">{#statementLanguageEn#}</option>
            <option value="pt">{#statementLanguagePt#}</option>
          </select>
        </div>
        <div class="row">
          <div class="col-md-12">
            <div class="panel">
              <ul class="nav nav-tabs">
                <li class="active"><a href="#statement-source" data-toggle="tab">Source</a></li>
                <li><a id="statement-preview-link" href="#statement-preview" data-toggle="tab">Preview</a></li>
              </ul>

              <div class="tab-content">
                <div class="tab-pane active" id="statement-source">
                  <div id="wmd-button-bar-statement"></div>
                  <textarea class="wmd-input" id="wmd-input-statement" name="wmd-input-statement"></textarea>
                </div>

                <div class="tab-pane" id="statement-preview">
                  <h1 style="text-align: center;" class="title"></h1>
                  <div class="no-bottom-margin statement" id="wmd-preview-statement"></div>
                  <hr/>
                  <div><em>{#wordsSource#}: <span class="source"></span></em></div>
                  <div><em>{#wordsProblemsetter#}: <a class="problemsetter"></a></em></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="form-group  col-md-6" id="markdown-message-group">
            <label class="control-label" for="markdown-message">{#problemEditCommitMessage#}</label>
            <input id="markdown-message" name="message" type="text" class="form-control" />
          </div>
        </div>

        <div class="row">
          <div class="col-md-12">
            <button type='submit' class="btn btn-primary">{#problemEditFormUpdateMarkdown#}</button>
          </div>
        </div>
      </form>
    </div>
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
    <div class="panel panel-primary">
      <div class="panel-body">
        <form class="form">
          <div class="form-group">
            <label for="tag-name">{#wordsTags#}</label>
            <input id="tag-name" name="tag_name" value="" type="text" size="20" class="form-control" autocomplete="off" />
          </div>
          <div class="form-group">
            <div class="tag-list pull-left"></div>
          </div>
          <div class="form-group">
            <label for="tag-public">{#wordsPublic#}</label>
            <select id="tag-public" name="tag_public" class="form-control">
              <option value="0" selected="selected">{#wordsNo#}</option>
              <option value="1">{#wordsYes#}</option>
            </select>
          </div>

          <button class="btn btn-primary" type='submit'>{#wordsAddTag#}</button>
        </form>
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th>{#contestEditTagName#}</th>
            <th>{#contestEditTagPublic#}</th>
            <th>{#contestEditTagDelete#}</th>
          </tr>
        </thead>
        <tbody id="problem-tags"></tbody>
      </table>
    </div>
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

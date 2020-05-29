<template>
  <div>
    <div class="page-header">
      <h1>
        <span>{{ T.problemEditEditProblem }} {{ data.title }}</span>
        <small>
          &ndash;
          <a v-bind:href="`/arena/problem/${alias}/`">
            {{ T.problemEditGoToProblem }}
          </a>
        </small>
      </h1>
      <p>
        <a
          href="https://github.com/omegaup/omegaup/wiki/C%C3%B3mo-escribir-problemas-para-Omegaup"
          >{{ T.navHelp }}</a
        >
      </p>
    </div>
    <ul class="nav nav-tabs">
      <li class="nav-item" v-on:click="showTab = 'edit'">
        <a href="#" data-toggle="tab" class="nav-link active">{{
          T.problemEditEditProblem
        }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'markdown'">
        <a href="#" data-toggle="tab" class="nav-link">{{
          T.problemEditEditMarkdown
        }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'version'">
        <a href="#" data-toggle="tab" class="nav-link">{{
          T.problemEditChooseVersion
        }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'solution'">
        <a href="#" data-toggle="tab" class="nav-link">{{
          T.problemEditSolution
        }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'admins'">
        <a href="#" data-toggle="tab" class="nav-link">{{
          T.problemEditAddAdmin
        }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'tags'">
        <a href="#" data-toggle="tab" class="nav-link">{{
          T.problemEditAddTags
        }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'download'">
        <a href="#" data-toggle="tab" class="nav-link">{{ T.wordsDownload }}</a>
      </li>
      <li class="nav-item" v-on:click="showTab = 'delete'">
        <a href="#" data-toggle="tab" class="nav-link">{{ T.wordsDelete }}</a>
      </li>
    </ul>

    <div class="tab-content">
      <div class="tab-pane active" v-if="showTab === 'edit'">
        <omegaup-problem-form
          v-bind:data="data"
          v-bind:original-visibility="data.originalVisibility"
          v-bind:is-update="true"
        ></omegaup-problem-form>
      </div>

      <div class="tab-pane active" v-if="showTab === 'markdown'">
        <omegaup-problem-markdown
          v-bind:markdown-contents="markdownContents"
          v-bind:markdown-preview="markdownPreview"
          v-bind:initial-language="data.statement.language"
          v-bind:markdown-type="'statements'"
          v-bind:alias="data.alias"
          v-bind:title="data.title"
          v-bind:source="data.source"
          v-bind:username="username"
          v-bind:name="name"
        ></omegaup-problem-markdown>
      </div>

      <div class="tab-pane active" v-if="showTab === 'version'">
        <omegaup-problem-versions
          v-bind:log="data.log"
          v-bind:published-revision="data.publishedRevision"
          v-bind:value="data.publishedRevision"
          v-bind:show-footer="true"
        ></omegaup-problem-versions>
      </div>

      <div class="tab-pane active" v-if="showTab === 'solution'">
        <omegaup-problem-markdown
          v-bind:markdown-contents="markdownSolutionContents"
          v-bind:markdown-preview="markdownSolutionPreview"
          v-bind:initial-language="data.solution.language"
          v-bind:markdown-type="'solutions'"
          v-bind:title="data.title"
        ></omegaup-problem-markdown>
      </div>

      <div class="tab-pane active" v-if="showTab === 'admins'">
        <omegaup-problem-admins
          v-bind:initial-admins="initialAdmins"
          v-bind:has-parent-component="true"
          v-on:emit-add-admin="
            addAdminComponent => $emit('add-admin', addAdminComponent.username)
          "
          v-on:emit-remove-admin="
            addAdminComponent =>
              $emit('remove-admin', addAdminComponent.selected.username)
          "
        ></omegaup-problem-admins>
        <omegaup-problem-group-admins
          v-bind:initial-groups="initialGroups"
          v-bind:has-parent-component="true"
          v-on:emit-add-group-admin="
            groupAdminsComponent =>
              $emit('add-group-admin', groupAdminsComponent.groupAlias)
          "
          v-on:emit-remove-group-admin="
            groupAdminsComponent =>
              $emit('remove-group-admin', groupAdminsComponent.groupAlias)
          "
        ></omegaup-problem-group-admins>
      </div>

      <div class="tab-pane active" v-if="showTab === 'tags'">
        <omegaup-problem-tags
          v-bind:initial-tags="data.tags"
          v-bind:initial-selected-tags="data.selectedTags"
          v-bind:alias="data.alias"
          v-bind:title="data.title"
          v-bind:initial-allow-tags="data.allowUserAddTags"
          v-bind:can-add-new-tags="true"
          v-on:emit-add-tag="
            (alias, tagname, isPublic) =>
              $emit('add-tag', alias, tagname, isPublic)
          "
          v-on:emit-remove-tag="
            (alias, tagname) => $emit('remove-tag', alias, tagname)
          "
          v-on:emit-change-allow-user-add-tag="
            (alias, title, allowTags) =>
              $emit('change-allow-user-add-tag', alias, title, allowTags)
          "
        ></omegaup-problem-tags>
      </div>

      <div class="tab-pane active" v-if="showTab === 'download'">
        <div class="card">
          <div class="card-body">
            <form class="form">
              <div class="form-group">
                <button class="btn btn-primary" type="submit">
                  {{ T.wordsDownload }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="tab-pane active" v-if="showTab === 'delete'">
        <div class="card">
          <div class="card-body">
            <form class="form">
              <div class="form-group">
                <div class="alert alert-danger">
                  <h4 class="alert-heading">{{ T.wordsDangerZone }}</h4>
                  <hr />
                  <span v-html="T.wordsDangerZoneDesc"></span>
                  <br /><br />
                  <button class="btn btn-danger" type="submit">
                    {{ T.wordsDelete }}
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import problem_Form from './Form.vue';
import problem_Tags from './Tags.vue';
import problem_Versions from './Versions.vue';
import problem_StatementEdit from './StatementEdit.vue';
import problem_Admins from '../common/Admins.vue';
import problem_GroupAdmins from '../common/GroupAdmins.vue';
import T from '../../lang';
import * as ui from '../../ui';
import latinize from 'latinize';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-form': problem_Form,
    'omegaup-problem-tags': problem_Tags,
    'omegaup-problem-versions': problem_Versions,
    'omegaup-problem-markdown': problem_StatementEdit,
    'omegaup-problem-admins': problem_Admins,
    'omegaup-problem-group-admins': problem_GroupAdmins,
  },
})
export default class ProblemEdit extends Vue {
  @Prop() data!: types.ProblemFormPayload;
  @Prop() initialAdmins!: types.ProblemAdmin[];
  @Prop() initialGroups!: types.ProblemGroupAdmin[];
  @Prop() markdownContents!: string;
  @Prop() markdownPreview!: string;
  @Prop() initialLanguage!: string;
  @Prop() username!: string;
  @Prop() name!: string;

  T = T;
  alias = this.data.alias;
  showTab = 'edit';
}
</script>

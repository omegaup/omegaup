<template>
  <div>
    <div class="page-header">
      <h1>
        <span>{{ T.problemEditEditProblem }} {{ data.title }}</span>
        <small>
          &ndash;
          <a :href="`/arena/problem/${alias}/`">
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
    <ul class="nav nav-pills edit-problem-tabs mb-3">
      <li class="nav-item dropdown">
        <a
          href="#"
          data-toggle="dropdown"
          role="button"
          class="nav-link active dropdown-toggle"
          aria-haspopup="true"
          aria-expanded="false"
          >{{ activeTab }}</a
        >
        <div class="dropdown-menu">
          <a
            href="#"
            data-toggle="tab"
            data-tab-edit
            class="dropdown-item"
            :class="{ active: showTab === 'edit' }"
            @click="showTab = 'edit'"
            >{{ T.problemEditEditProblem }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-markdown
            class="dropdown-item"
            :class="{ active: showTab === 'markdown' }"
            @click="showTab = 'markdown'"
            >{{ T.problemEditEditMarkdown }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-version
            class="dropdown-item"
            :class="{ active: showTab === 'version' }"
            @click="showTab = 'version'"
            >{{ T.problemEditChooseVersion }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-solution
            class="dropdown-item"
            :class="{ active: showTab === 'solution' }"
            @click="showTab = 'solution'"
            >{{ T.problemEditSolution }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-admins
            class="dropdown-item"
            :class="{ active: showTab === 'admins' }"
            @click="showTab = 'admins'"
            >{{ T.problemEditAddAdmin }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-tags
            class="dropdown-item"
            :class="{ active: showTab === 'tags' }"
            @click="showTab = 'tags'"
            >{{ T.problemEditAddTags }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-download
            class="dropdown-item"
            :class="{ active: showTab === 'download' }"
            @click="showTab = 'download'"
            >{{ T.wordsDownload }}</a
          >
          <a
            href="#"
            data-toggle="tab"
            data-tab-delete
            class="dropdown-item"
            :class="{ active: showTab === 'delete' }"
            @click="showTab = 'delete'"
            >{{ T.wordsDelete }}</a
          >
        </div>
      </li>
    </ul>

    <div class="tab-content">
      <div v-if="showTab === 'edit'" class="tab-pane active">
        <omegaup-problem-form
          :data="data"
          :original-visibility="data.originalVisibility"
          :is-update="true"
        ></omegaup-problem-form>
      </div>

      <div v-if="showTab === 'markdown'" class="tab-pane active">
        <omegaup-problem-statementedit
          :statement="currentStatement"
          markdown-type="statements"
          :alias="data.alias"
          :title="data.title"
          :source="data.source"
          :problemsetter="data.problemsetter"
          @update-markdown-contents="
            (statements, newLanguage, currentMarkdown) =>
              $emit(
                'update-markdown-contents',
                statements,
                newLanguage,
                currentMarkdown,
                'statements',
              )
          "
        ></omegaup-problem-statementedit>
      </div>

      <div v-if="showTab === 'version'" class="tab-pane active">
        <omegaup-problem-versions
          :log="data.log"
          :published-revision="data.publishedRevision"
          :value="data.publishedRevision"
          :show-footer="true"
          @emit-select-version="
            (selectedRevision, updatePublished) =>
              $emit('select-version', selectedRevision, updatePublished)
          "
          @emit-runs-diff="
            (addProblemComponent, selectedCommit) =>
              $emit('runs-diff', addProblemComponent, selectedCommit)
          "
        ></omegaup-problem-versions>
      </div>

      <div v-if="showTab === 'solution'" class="tab-pane active">
        <omegaup-problem-statementedit
          :statement="
            data.solution || { markdown: '', language: 'es', images: {} }
          "
          markdown-type="solutions"
          :title="data.title"
          @update-markdown-contents="
            (solutions, newLanguage, currentMarkdown) =>
              $emit(
                'update-markdown-contents',
                solutions,
                newLanguage,
                currentMarkdown,
                'solutions',
              )
          "
        ></omegaup-problem-statementedit>
      </div>

      <div v-if="showTab === 'admins'" class="tab-pane active">
        <omegaup-common-admins
          :admins="admins"
          :search-result-users="searchResultUsers"
          @add-admin="(username) => $emit('add-admin', username)"
          @remove-admin="(username) => $emit('remove-admin', username)"
          @update-search-result-users="
            (query) => $emit('update-search-result-users', query)
          "
        ></omegaup-common-admins>
        <omegaup-common-groupadmins
          :group-admins="groups"
          :search-result-groups="searchResultGroups"
          @add-group-admin="
            (groupAlias) => $emit('add-group-admin', groupAlias)
          "
          @remove-group-admin="
            (groupAlias) => $emit('remove-group-admin', groupAlias)
          "
          @update-search-result-groups="
            (query) => $emit('update-search-result-groups', query)
          "
        ></omegaup-common-groupadmins>
      </div>

      <div v-if="showTab === 'tags'" class="tab-pane active">
        <omegaup-problem-tags
          :alias="data.alias"
          :title="data.title"
          :initial-allow-tags="data.allowUserAddTags"
          :can-add-new-tags="true"
          :public-tags="data.publicTags"
          :level-tags="data.levelTags"
          :problem-level="data.problemLevel"
          :selected-public-tags="data.selectedPublicTags"
          :selected-private-tags="data.selectedPrivateTags"
          @emit-update-problem-level="
            (levelTag) => $emit('update-problem-level', levelTag)
          "
          @emit-add-tag="
            (alias, tagname, isPublic) =>
              $emit('add-tag', alias, tagname, isPublic)
          "
          @emit-remove-tag="
            (alias, tagname, isPublic) =>
              $emit('remove-tag', alias, tagname, isPublic)
          "
          @emit-change-allow-user-add-tag="
            (alias, title, allowTags) =>
              $emit('change-allow-user-add-tag', alias, title, allowTags)
          "
        ></omegaup-problem-tags>
      </div>

      <div v-if="showTab === 'download'" class="tab-pane active">
        <div class="card">
          <div class="card-body">
            <form class="form" @submit.prevent="onDownload">
              <div class="form-group">
                <button class="btn btn-primary" type="submit">
                  {{ T.wordsDownload }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div v-if="showTab === 'delete'" class="tab-pane active">
        <div class="card">
          <div class="card-body">
            <div class="form-group">
              <div class="alert alert-danger">
                <h4 class="alert-heading">{{ T.wordsDangerZone }}</h4>
                <hr />
                <omegaup-markdown
                  :markdown="T.wordsDangerZoneDesc"
                ></omegaup-markdown>
                <br /><br />
                <button
                  class="btn btn-danger"
                  @click.prevent="showConfirmationModal = true"
                >
                  {{ T.wordsDelete }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <b-modal
      v-model="showConfirmationModal"
      :title="T.problemEditDeleteRequireConfirmation"
      :ok-title="T.problemEditDeleteOk"
      ok-variant="danger"
      :cancel-title="T.problemEditDeleteCancel"
      @ok="$emit('remove', alias)"
    >
      <p>{{ T.problemEditDeleteConfirmationMessage }}</p>
    </b-modal>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import problem_Form from './Form.vue';
import problem_Tags from './Tags.vue';
import problem_Versions from './Versions.vue';
import problem_StatementEdit from './StatementEdit.vue';
import common_Admins from '../common/Admins.vue';
import common_GroupAdmins from '../common/GroupAdmins.vue';
import T from '../../lang';
import { types } from '../../api_types';
import omegaup_Markdown from '../Markdown.vue';

import 'bootstrap-vue/dist/bootstrap-vue.css';
import { ModalPlugin } from 'bootstrap-vue';
Vue.use(ModalPlugin);

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
    'omegaup-problem-form': problem_Form,
    'omegaup-problem-tags': problem_Tags,
    'omegaup-problem-versions': problem_Versions,
    'omegaup-problem-statementedit': problem_StatementEdit,
    'omegaup-common-admins': common_Admins,
    'omegaup-common-groupadmins': common_GroupAdmins,
  },
})
export default class ProblemEdit extends Vue {
  @Prop() data!: types.ProblemEditPayload;
  @Prop() admins!: types.ProblemAdmin[];
  @Prop() groups!: types.ProblemGroupAdmin[];
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop() statement!: types.ProblemStatement;
  @Prop() searchResultUsers!: types.ListItem[];
  @Prop() searchResultGroups!: types.ListItem[];

  T = T;
  alias = this.data.alias;
  showTab = 'edit';
  currentStatement: types.ProblemStatement = this.statement;
  showConfirmationModal = false;

  get activeTab(): string {
    switch (this.showTab) {
      case 'edit':
        return T.problemEditEditProblem;
      case 'markdown':
        return T.problemEditEditMarkdown;
      case 'version':
        return T.problemEditChooseVersion;
      case 'solution':
        return T.problemEditSolution;
      case 'admins':
        return T.problemEditAddAdmin;
      case 'tags':
        return T.problemEditAddTags;
      case 'download':
        return T.wordsDownload;
      case 'delete':
        return T.wordsDelete;
      default:
        return T.problemEditEditProblem;
    }
  }

  onDownload(): void {
    window.location.href = `/api/problem/download/problem_alias/${this.alias}/`;
  }

  @Watch('statement')
  onStatementChange(newStatement: types.ProblemStatement): void {
    this.currentStatement = newStatement;
  }
}
</script>

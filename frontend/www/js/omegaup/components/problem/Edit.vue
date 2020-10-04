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
          :statement="data.statement"
          markdown-type="statements"
          :alias="data.alias"
          :title="data.title"
          :source="data.source"
          :problemsetter="data.problemsetter"
          @emit-update-markdown-contents="
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
          @emit-update-markdown-contents="
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
        <omegaup-problem-admins
          :initial-admins="initialAdmins"
          :has-parent-component="true"
          @emit-add-admin="
            (addAdminComponent) =>
              $emit('add-admin', addAdminComponent.username)
          "
          @emit-remove-admin="
            (addAdminComponent) =>
              $emit('remove-admin', addAdminComponent.selected.username)
          "
        ></omegaup-problem-admins>
        <omegaup-problem-groupadmins
          :initial-groups="initialGroups"
          :has-parent-component="true"
          @emit-add-group-admin="
            (groupAdminsComponent) =>
              $emit('add-group-admin', groupAdminsComponent.groupAlias)
          "
          @emit-remove-group-admin="
            (groupAdminsComponent) =>
              $emit('remove-group-admin', groupAdminsComponent.groupAlias)
          "
        ></omegaup-problem-groupadmins>
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
            <form class="form" @submit.prevent="$emit('remove', alias)">
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
import { Vue, Component, Prop } from 'vue-property-decorator';
import problem_Form from './Form.vue';
import problem_Tags from './Tags.vue';
import problem_Versions from './Versions.vue';
import problem_StatementEdit from './StatementEdit.vue';
import problem_Admins from '../common/Admins.vue';
import problem_GroupAdmins from '../common/GroupAdmins.vue';
import T from '../../lang';
import { types } from '../../api_types';

@Component({
  components: {
    'omegaup-problem-form': problem_Form,
    'omegaup-problem-tags': problem_Tags,
    'omegaup-problem-versions': problem_Versions,
    'omegaup-problem-statementedit': problem_StatementEdit,
    'omegaup-problem-admins': problem_Admins,
    'omegaup-problem-groupadmins': problem_GroupAdmins,
  },
})
export default class ProblemEdit extends Vue {
  @Prop() data!: types.ProblemEditPayload;
  @Prop() initialAdmins!: types.ProblemAdmin[];
  @Prop() initialGroups!: types.ProblemGroupAdmin[];
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop() statement!: types.ProblemStatement;

  T = T;
  alias = this.data.alias;
  showTab = 'edit';

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
}
</script>

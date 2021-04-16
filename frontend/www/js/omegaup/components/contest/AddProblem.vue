<template>
  <div class="card problems-container">
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-12">
            <label class="font-weight-bold">{{ T.wordsProblem }}</label>
            <input
              v-if="isUpdate"
              :value="title"
              class="form-control"
              disabled="disabled"
            />
            <omegaup-common-typeahead
              v-else
              :existing-options="searchResultProblems"
              :value.sync="alias"
              @update-existing-options="
                (query) => $emit('update-search-result-problems', query)
              "
            >
            </omegaup-common-typeahead>
          </div>
        </div>
        <div v-if="alias" class="row">
          <div class="form-group col-md-6">
            <label for="use-latest-version" class="font-weight-bold"
              >{{ T.contestAddproblemChooseVersion }}
            </label>
            <omegaup-radio-switch
              :value.sync="useLatestVersion"
              :selected-value="useLatestVersion"
              :name="'use-latest-version'"
              :text-for-true="T.contestAddproblemLatestVersion"
              :text-for-false="T.contestAddproblemOtherVersion"
            ></omegaup-radio-switch>
          </div>
          <div class="form-group col-md-3">
            <label
              v-tooltip="T.contestAddproblemProblemPoints"
              class="font-weight-bold"
              >{{ T.wordsPoints }}
              <font-awesome-icon icon="info-circle" />
            </label>
            <input
              v-model="points"
              class="form-control problem-points"
              size="3"
              type="number"
            />
          </div>
          <div class="form-group col-md-3">
            <label
              v-tooltip="T.contestAddproblemContestOrder"
              class="font-weight-bold"
              >{{ T.contestAddproblemProblemOrder }}
              <font-awesome-icon icon="info-circle" />
            </label>
            <input
              v-model="order"
              class="form-control"
              max="100"
              size="2"
              type="number"
            />
          </div>
        </div>
        <template v-if="!useLatestVersion && alias !== null">
          <div class="form-group">
            <button
              class="btn btn-primary get-versions"
              type="submit"
              :disabled="alias === null"
              @click.prevent="onSubmit"
            >
              {{ T.wordsGetVersions }}
            </button>
            <small class="form-text text-muted">
              {{ T.selectProblemToGetVersions }}
            </small>
          </div>
          <omegaup-problem-versions
            v-model="selectedRevision"
            :log="versionLog"
            :published-revision="publishedRevision"
            :show-footer="false"
            @runs-diff="onRunsDiff"
          ></omegaup-problem-versions>
        </template>
        <div class="form-group">
          <button
            class="btn btn-primary add-problem"
            type="submit"
            :disabled="addProblemButtonDisabled"
            @click.prevent="onAddProblem"
          >
            {{ addProblemButtonLabel }}
          </button>
          <button
            class="btn btn-secondary mx-3"
            type="reset"
            :disabled="addProblemButtonDisabled"
            @click.prevent="alias = null"
          >
            {{ T.wordsCancel }}
          </button>
        </div>
      </form>
    </div>
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th class="text-center">{{ T.contestAddproblemContestOrder }}</th>
          <th class="text-center">{{ T.contestAddproblemProblemName }}</th>
          <th class="text-center">{{ T.contestAddproblemProblemPoints }}</th>
          <th class="text-center">{{ T.wordsActions }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="problem in problems" :key="problem.alias">
          <td class="text-center">{{ problem.order }}</td>
          <td>
            <a :href="`/arena/problem/${problem.alias}/`">{{
              problem.title
            }}</a>
          </td>
          <td class="text-right">{{ problem.points }}</td>
          <td class="text-center">
            <button
              v-tooltip="T.problemEditFormUpdateProblem"
              :data-update-problem="problem.alias"
              class="btn btn-link"
              @click="onEdit(problem)"
            >
              <font-awesome-icon icon="edit" />
            </button>
            <button
              v-if="problem.has_submissions"
              v-tooltip="T.cannotRemoveProblemWithSubmissions"
              :data-remove-problem-disabled="problem.alias"
              class="btn btn-link"
              data-toggle="tooltip"
              data-placement="bottom"
            >
              <font-awesome-icon icon="trash" class="disabled text-secondary" />
            </button>
            <button
              v-else
              v-tooltip="T.contestAddproblemProblemRemove"
              :data-remove-problem="problem.alias"
              class="btn btn-link"
              @click="onRemove(problem)"
            >
              <font-awesome-icon icon="trash" />
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

import problem_Versions from '../problem/Versions.vue';
import common_Typeahead from '../common/Typeahead.vue';
import omegaup_RadioSwitch from '../RadioSwitch.vue';
import 'v-tooltip/dist/v-tooltip.css';
import { VTooltip } from 'v-tooltip';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'omegaup-problem-versions': problem_Versions,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-radio-switch': omegaup_RadioSwitch,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
  directives: {
    tooltip: VTooltip,
  },
})
export default class AddProblem extends Vue {
  @Prop() contestAlias!: string;
  @Prop() initialPoints!: number;
  @Prop() initialProblems!: types.ProblemsetProblem[];
  @Prop() searchResultProblems!: types.ListItem[];

  T = T;
  alias: null | string = null;
  title: null | string = null;
  points = this.initialPoints;
  order = this.initialProblems.length + 1;
  problems = this.initialProblems;
  versionLog: types.ProblemVersion[] = [];
  useLatestVersion = true;
  publishedRevision: null | types.ProblemVersion = null;
  selectedRevision: null | types.ProblemVersion = null;

  onSubmit(): void {
    if (this.useLatestVersion) {
      this.$emit('get-versions', this.alias, this);
    } else {
      this.onAddProblem();
    }
  }

  onAddProblem(): void {
    this.$emit('add-problem', {
      problem: {
        order: this.order,
        alias: this.alias,
        points: this.points,
        commit: !this.useLatestVersion
          ? this.selectedRevision?.commit
          : undefined,
      },
      isUpdate: this.isUpdate,
    });
    this.alias = null;
    this.title = null;
  }

  onEdit(problem: types.ProblemsetProblem): void {
    this.title = problem.title;
    this.alias = problem.alias;
    this.points = problem.points;
    this.order = problem.order;
    this.useLatestVersion = false;
  }

  onRemove(problem: types.ProblemsetProblem): void {
    this.$emit('remove-problem', problem.alias);
  }

  onRunsDiff(
    versions: types.ProblemVersion[],
    selectedCommit: types.ProblemVersion,
  ): void {
    let found = false;
    for (const problem of this.problems) {
      if (this.alias === problem.alias) {
        found = true;
        break;
      }
    }
    if (!found) {
      return;
    }
    this.$emit('runs-diff', this.alias, versions, selectedCommit);
  }

  get isUpdate(): boolean {
    return this.problems.some((problem) => problem.alias === this.alias);
  }

  get addProblemButtonLabel(): string {
    if (this.isUpdate) {
      return T.wordsUpdateProblem;
    }
    return T.wordsAddProblem;
  }

  get addProblemButtonDisabled(): boolean {
    if (this.useLatestVersion) return this.alias === null;
    return !this.selectedRevision;
  }

  @Watch('initialProblems')
  onInitialProblemsChange(newValue: types.ProblemsetProblem[]): void {
    this.problems = newValue;
    this.order = newValue.length + 1;
  }

  @Watch('alias')
  onAliasChange(newProblemAlias: string) {
    if (!newProblemAlias) {
      this.versionLog = [];
      this.selectedRevision = this.publishedRevision = null;
      return;
    }
    this.$emit('get-versions', newProblemAlias, this);
  }
}
</script>

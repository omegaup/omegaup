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
            <div v-else class="input-group w-100">
              <div class="input-group-prepend w-25">
                <select v-model="selectedSearchType" class="custom-select">
                  <option
                    v-for="searchType in availableSearchTypes"
                    :key="searchType.key"
                    :value="searchType.key"
                    :selected="selectedSearchType === searchType.key"
                  >
                    {{ searchType.value }}
                  </option>
                </select>
              </div>
              <omegaup-common-typeahead
                class="w-75"
                :existing-options="searchResultProblems"
                :activation-threshold="1"
                :value.sync="alias"
                @update-existing-options="
                  (query) =>
                    $emit('update-search-result-problems', {
                      query,
                      searchType: selectedSearchType,
                    })
                "
              >
              </omegaup-common-typeahead>
            </div>
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
        <omegaup-problem-versions
          v-if="!useLatestVersion && alias !== null"
          v-model="selectedRevision"
          :log="versionLog"
          :published-revision="publishedRevision"
          :show-footer="false"
          @runs-diff="onRunsDiff"
        ></omegaup-problem-versions>
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

interface MappedProblems {
  [problemAlias: string]: {
    problem: types.ProblemsetProblemWithVersions;
    commitVersions: { [commit: string]: types.ProblemVersion };
  };
}

export enum SearchTypes {
  ALL = 'all',
  TITLE = 'title',
  ALIAS = 'alias',
  ID = 'problem_id',
}

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
  @Prop() initialProblems!: types.ProblemsetProblemWithVersions[];
  @Prop() searchResultProblems!: types.ListItem[];

  T = T;
  alias: null | types.ListItem = null;
  title: null | string = null;
  points = this.initialPoints;
  order = this.initialProblems.length + 1;
  problems = this.initialProblems;
  versionLog: types.ProblemVersion[] = [];
  useLatestVersion = true;
  publishedRevision: null | types.ProblemVersion = null;
  selectedRevision: null | types.ProblemVersion = null;
  selectedSearchType: SearchTypes = SearchTypes.ALL;
  availableSearchTypes: types.ListItem[] = [
    { key: SearchTypes.ALL, value: T.contestEditAddProblemSearchByAll },
    { key: SearchTypes.ALIAS, value: T.contestEditAddProblemSearchByAlias },
    { key: SearchTypes.TITLE, value: T.contestEditAddProblemSearchByTitle },
    { key: SearchTypes.ID, value: T.contestEditAddProblemSearchById },
  ];

  get problemMapping(): MappedProblems {
    let problemMapping: MappedProblems = {};
    for (const problem of this.problems) {
      const commitVersions: { [commit: string]: types.ProblemVersion } = {};
      for (const version of problem.versions.log) {
        commitVersions[version.commit] = version;
      }
      problemMapping[problem.alias] = {
        problem,
        commitVersions,
      };
    }
    return problemMapping;
  }

  onGetVersions(problemAlias: string): void {
    const problemMapping = this.problemMapping[problemAlias];
    this.versionLog = problemMapping.problem.versions.log;
    const published = problemMapping.problem.commit;
    const revision = problemMapping.commitVersions[published];
    this.selectedRevision = this.publishedRevision = revision;
    this.useLatestVersion = false;
  }

  onSubmit(): void {
    if (!this.alias) return;
    this.onAddProblem();
  }

  onAddProblem(): void {
    this.$emit('add-problem', {
      problem: {
        order: this.order,
        alias: this.alias?.key,
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

  onEdit(problem: types.ProblemsetProblemWithVersions): void {
    this.title = problem.title;
    this.alias = { key: problem.alias, value: problem.title };
    this.points = problem.points;
    this.order = problem.order;
  }

  onRemove(problem: types.ProblemsetProblemWithVersions): void {
    this.$emit('remove-problem', problem.alias);
  }

  onRunsDiff(
    versions: types.ProblemVersion[],
    selectedCommit: types.ProblemVersion,
  ): void {
    let found = false;
    for (const problem of this.problems) {
      if (this.alias?.key === problem.alias) {
        found = true;
        break;
      }
    }
    if (!found) {
      return;
    }
    this.$emit('runs-diff', this.alias?.key, versions, selectedCommit);
  }

  get isUpdate(): boolean {
    if (!this.alias) return false;
    return !!this.problemMapping[this.alias.key];
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
  onInitialProblemsChange(
    newValue: types.ProblemsetProblemWithVersions[],
  ): void {
    this.problems = newValue;
    this.order = newValue.length + 1;
  }

  @Watch('alias')
  onAliasChange(newProblemAlias: null | types.ListItem) {
    if (!newProblemAlias) {
      this.versionLog = [];
      this.selectedRevision = this.publishedRevision = null;
      return;
    }
    if (this.isUpdate) {
      this.onGetVersions(newProblemAlias.key);
      return;
    }
    this.$emit('get-versions', {
      target: this,
      request: { problemAlias: this.alias?.key },
    });
  }
}
</script>

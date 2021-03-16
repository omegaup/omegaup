<template>
  <div class="card problems-container">
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.wordsProblem }}</label>
            <omegaup-common-typeahead
              :existing-options="existingProblems"
              :type="'problem'"
              :value.sync="alias"
              @update-existing-options="
                (query) => $emit('update-existing-problems', query)
              "
            >
            </omegaup-common-typeahead>
          </div>
          <div class="form-group col-md-6">
            <label for="use-latest-version">{{
              T.contestAddproblemChooseVersion
            }}</label>
            <div class="form-control">
              <div class="form-check form-check-inline">
                <label class="form-check-label">
                  <input
                    v-model="useLatestVersion"
                    class="form-check-input"
                    type="radio"
                    name="use-latest-version"
                    :value="true"
                  />
                  {{ T.contestAddproblemLatestVersion }}
                </label>
              </div>
              <div class="form-check form-check-inline">
                <label class="form-check-label">
                  <input
                    v-model="useLatestVersion"
                    class="form-check-input"
                    type="radio"
                    name="use-latest-version"
                    :value="false"
                  />
                  {{ T.contestAddproblemOtherVersion }}
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.contestAddproblemProblemPoints }}</label>
            <input
              v-model="points"
              class="form-control problem-points"
              size="3"
            />
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestAddproblemContestOrder }}</label>
            <input
              v-model="order"
              class="form-control"
              max="100"
              size="2"
              type="number"
            />
          </div>
        </div>
        <div v-show="!useLatestVersion" class="form-group">
          <button
            class="btn btn-primary get-versions"
            type="submit"
            :disabled="alias == ''"
            @click.prevent="onSubmit"
          >
            {{ T.wordsGetVersions }}
          </button>
          <small class="form-text text-muted">
            {{ T.selectProblemToGetVersions }}
          </small>
        </div>
        <omegaup-problem-versions
          v-show="!useLatestVersion"
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
        </div>
      </form>
    </div>
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th></th>
          <th class="text-center">{{ T.contestAddproblemContestOrder }}</th>
          <th class="text-center">{{ T.contestAddproblemProblemName }}</th>
          <th class="text-center">{{ T.contestAddproblemProblemPoints }}</th>
          <th class="text-center">{{ T.contestAddproblemProblemRemove }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="problem in problems" :key="problem.alias">
          <td>
            <button
              class="btn btn-default"
              type="button"
              :aria-label="T.wordsEdit"
              @click.prevent="onEdit(problem)"
            >
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-pencil"
              ></span>
            </button>
          </td>
          <td class="text-center">{{ problem.order }}</td>
          <td>
            <a :href="`/arena/problem/${problem.alias}/`">{{
              problem.alias
            }}</a>
          </td>
          <td class="text-right">{{ problem.points }}</td>
          <td class="text-center">
            <button class="close float-none" @click="onRemove(problem)">
              ×
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

const emptyCommit: types.ProblemVersion = {
  author: {},
  commit: '',
  committer: {},
  message: '',
  parents: [],
  tree: {},
  version: '',
};

@Component({
  components: {
    'omegaup-problem-versions': problem_Versions,
    'omegaup-common-typeahead': common_Typeahead,
  },
})
export default class AddProblem extends Vue {
  @Prop() contestAlias!: string;
  @Prop() initialPoints!: number;
  @Prop() initialProblems!: types.ProblemsetProblem[];
  @Prop() existingProblems!: { key: string; value: string }[];

  T = T;
  alias = '';
  points = this.initialPoints;
  order = this.initialProblems.length + 1;
  problems = this.initialProblems;
  versionLog: types.ProblemVersion[] = [];
  useLatestVersion = true;
  publishedRevision = emptyCommit;
  selectedRevision = emptyCommit;

  onSubmit(): void {
    if (this.useLatestVersion) {
      this.$emit('get-versions', this.alias, this);
    } else {
      this.onAddProblem();
    }
  }

  onAddProblem(): void {
    this.$emit('add-problem', {
      order: this.order,
      alias: this.alias,
      points: this.points,
      commit:
        !this.useLatestVersion && this.selectedRevision
          ? this.selectedRevision.commit
          : undefined,
    });
  }

  onEdit(problem: types.ProblemsetProblem): void {
    this.alias = problem.alias;
    this.points = problem.points;
    this.order = problem.order;
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

  get addProblemButtonLabel(): string {
    for (const problem of this.problems) {
      if (this.alias === problem.alias) {
        return T.wordsUpdateProblem;
      }
    }
    return T.wordsAddProblem;
  }

  get addProblemButtonDisabled(): boolean {
    if (this.useLatestVersion) {
      return this.alias === '' || this.alias === null;
    }
    return this.selectedRevision.commit === '';
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
      this.selectedRevision = this.publishedRevision = emptyCommit;
      return;
    }
    this.$emit('get-versions', newProblemAlias, this);
  }
}
</script>

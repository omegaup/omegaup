<template>
  <div class="card">
    <div class="card-body">
      <form class="form" v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-6">
            <label>{{ T.wordsProblem }}</label>
            <omegaup-autocomplete
              v-bind:init="(el) => typeahead.problemTypeahead(el)"
              v-model="alias"
            ></omegaup-autocomplete>
          </div>
          <div class="form-group col-md-6">
            <label for="use-latest-version">{{
              T.contestAddproblemChooseVersion
            }}</label>
            <div class="form-control">
              <div class="form-check form-check-inline">
                <label class="form-check-label">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="use-latest-version"
                    v-model="useLatestVersion"
                    v-bind:value="true"
                  />
                  {{ T.contestAddproblemLatestVersion }}
                </label>
              </div>
              <div class="form-check form-check-inline">
                <label class="form-check-label">
                  <input
                    class="form-check-input"
                    type="radio"
                    name="use-latest-version"
                    v-model="useLatestVersion"
                    v-bind:value="false"
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
              class="form-control problem-points"
              size="3"
              v-model="points"
            />
          </div>
          <div class="form-group col-md-6">
            <label>{{ T.contestAddproblemContestOrder }}</label>
            <input
              class="form-control"
              max="100"
              size="2"
              type="number"
              v-model="order"
            />
          </div>
        </div>
        <div class="form-group" v-show="!useLatestVersion">
          <button
            class="btn btn-primary get-versions"
            type="submit"
            v-bind:disabled="alias == ''"
            v-on:click.prevent="onSubmit"
          >
            {{ T.wordsGetVersions }}
          </button>
          <small class="form-text text-muted">
            {{ T.selectProblemToGetVersions }}
          </small>
        </div>
        <omegaup-problem-versions
          v-bind:log="versionLog"
          v-bind:published-revision="publishedRevision"
          v-bind:show-footer="false"
          v-model="selectedRevision"
          v-on:runs-diff="onRunsDiff"
          v-show="!useLatestVersion"
        ></omegaup-problem-versions>
        <div class="form-group">
          <button
            class="btn btn-primary add-problem"
            type="submit"
            v-on:click.prevent="onAddProblem"
            v-bind:disabled="addProblemButtonDisabled"
          >
            {{ addProblemButtonLabel }}
          </button>
        </div>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th></th>
          <th>{{ T.contestAddproblemContestOrder }}</th>
          <th>{{ T.contestAddproblemProblemName }}</th>
          <th>{{ T.contestAddproblemProblemPoints }}</th>
          <th>{{ T.contestAddproblemProblemRemove }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-bind:key="problem.alias" v-for="problem in problems">
          <td>
            <button
              class="btn btn-default"
              type="button"
              v-bind:aria-label="T.wordsEdit"
              v-on:click.prevent="onEdit(problem)"
            >
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-pencil"
              ></span>
            </button>
          </td>
          <td>{{ problem.order }}</td>
          <td>
            <a v-bind:href="`/arena/problem/${problem.alias}/`">{{
              problem.alias
            }}</a>
          </td>
          <td>{{ problem.points }}</td>
          <td>
            <button class="close" v-on:click="onRemove(problem)">×</button>
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
import * as typeahead from '../../typeahead';
import Autocomplete from '../Autocomplete.vue';
import problem_Versions from '../problem/Versions.vue';

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
    'omegaup-autocomplete': Autocomplete,
    'omegaup-problem-versions': problem_Versions,
  },
})
export default class AddProblem extends Vue {
  @Prop() contestAlias!: string;
  @Prop() initialPoints!: number;
  @Prop() initialProblems!: types.ContestProblem[];

  T = T;
  typeahead = typeahead;
  alias = '';
  points = this.initialPoints;
  order = this.initialProblems.length + 1;
  problems = this.initialProblems;
  selected: types.ContestProblem = {
    accepted: 0,
    alias: '',
    commit: '',
    difficulty: 0,
    languages: '',
    order: 1,
    points: this.points,
    problem_id: 0,
    submissions: 0,
    title: '',
    version: '',
    visibility: 0,
    visits: 0,
  };
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

  onEdit(problem: types.ContestProblem): void {
    this.alias = problem.alias;
    this.points = problem.points;
    this.order = problem.order;
  }

  onRemove(problem: types.ContestProblem): void {
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
      return this.alias === '';
    }
    return this.selectedRevision.commit === '';
  }

  @Watch('initialProblems')
  onInitialProblemsChange(newValue: types.ContestProblem[]): void {
    this.problems = newValue;
    this.alias = '';
    this.points = this.points;
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

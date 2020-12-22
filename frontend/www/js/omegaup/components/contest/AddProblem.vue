<template>
  <div class="panel panel-primary problems-container">
    <div class="panel-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group col-md-6">
          <label>{{ T.wordsProblem }}</label>
          <omegaup-autocomplete
            v-model="alias"
            :init="(el) => typeahead.problemTypeahead(el)"
          ></omegaup-autocomplete>
        </div>
        <div class="form-group col-md-6">
          <label for="use-latest-version">{{
            T.contestAddproblemChooseVersion
          }}</label>
          <div class="form-control">
            <label class="radio-inline">
              <input
                v-model="useLatestVersion"
                type="radio"
                name="use-latest-version"
                :value="true"
              />
              {{ T.contestAddproblemLatestVersion }}
            </label>
            <label class="radio-inline">
              <input
                v-model="useLatestVersion"
                type="radio"
                name="use-latest-version"
                :value="false"
              />
              {{ T.contestAddproblemOtherVersion }}
            </label>
          </div>
        </div>
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
        <div v-show="!useLatestVersion" class="form-group col-md-12">
          <button
            class="btn btn-primary get-versions"
            type="submit"
            :disabled="alias == ''"
            @click.prevent="onSubmit"
          >
            {{ T.wordsGetVersions }}
          </button>
          <span class="label label-info">{{
            T.selectProblemToGetVersions
          }}</span>
        </div>
        <omegaup-problem-versions
          v-show="!useLatestVersion"
          v-model="selectedRevision"
          :log="versionLog"
          :published-revision="publishedRevision"
          :show-footer="false"
          @runs-diff="onRunsDiff"
        ></omegaup-problem-versions>
      </form>
      <div class="form-group col-md-12">
        <button
          class="btn btn-primary add-problem"
          type="submit"
          :disabled="addProblemButtonDisabled"
          @click.prevent="onAddProblem"
        >
          {{ addProblemButtonLabel }}
        </button>
      </div>
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
          <td>{{ problem.order }}</td>
          <td>
            <a :href="`/arena/problem/${problem.alias}/`">{{
              problem.alias
            }}</a>
          </td>
          <td>{{ problem.points }}</td>
          <td>
            <button class="close" @click="onRemove(problem)">×</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import { omegaup } from '../../omegaup';
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
  @Prop() data!: omegaup.Problem[];

  T = T;
  typeahead = typeahead;
  alias = '';
  points = this.initialPoints;
  order = this.data.length + 1;
  problems = this.data;
  selected: omegaup.Problem = {
    alias: '',
    order: 1,
    points: this.points,
    title: '',
    input_limit: 0,
  };
  versionLog: types.ProblemVersion[] = [];
  useLatestVersion = true;
  publishedRevision = emptyCommit;
  selectedRevision = emptyCommit;

  onSubmit(): void {
    if (this.useLatestVersion) {
      this.$emit('emit-change-alias', this, this.alias);
    } else {
      this.onAddProblem();
    }
  }

  onAddProblem(): void {
    this.$emit('emit-add-problem', this);
  }

  onEdit(problem: omegaup.Problem): void {
    this.alias = problem.alias;
    this.points = problem.points;
    this.order = problem.order;
  }

  onRemove(problem: omegaup.Problem): void {
    this.selected = problem;
    this.$emit('emit-remove-problem', this);
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
    this.$emit('emit-runs-diff', this, versions, selectedCommit);
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

  @Watch('problems')
  onProblemsChange(newValue: omegaup.Problem[]): void {
    this.alias = '';
    this.order = newValue.length + 1;
  }

  @Watch('alias')
  onAliasChange(newProblemAlias: string) {
    if (!newProblemAlias) {
      this.versionLog = [];
      this.selectedRevision = this.publishedRevision = emptyCommit;
      return;
    }
    this.$emit('emit-change-alias', this, newProblemAlias);
  }
}
</script>

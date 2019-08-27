<template>
  <div class="panel panel-primary problems-container">
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{T.wordsProblem}}</label> <omegaup-autocomplete v-bind:init=
          "el =&gt; UI.problemTypeahead(el)"
               v-model="alias"></omegaup-autocomplete>
        </div>
        <div class="form-group">
          <label>{{T.contestAddproblemProblemPoints}}</label> <input class=
          "form-control problem-points"
               size="3"
               v-model="points">
        </div>
        <div class="form-group">
          <label>{{T.contestAddproblemContestOrder}}</label> <input class="form-control"
               max="100"
               size="2"
               type="number"
               v-model="order">
        </div><omegaup-problem-versions v-bind:log="versionLog"
              v-bind:published-revision="publishedRevision"
              v-bind:show-footer="false"
              v-model="selectedRevision"
              v-on:runs-diff="onRunsDiff"></omegaup-problem-versions>
        <div class="form-group">
          <button class="btn btn-primary add-problem"
               type="submit">{{addProblemButtonLabel}}</button>
        </div>
      </form>
    </div>
    <table class="table table-striped">
      <thead>
        <tr>
          <th></th>
          <th>{{T.contestAddproblemContestOrder}}</th>
          <th>{{T.contestAddproblemProblemName}}</th>
          <th>{{T.contestAddproblemProblemPoints}}</th>
          <th>{{T.contestAddproblemProblemRemove}}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="problem in problems">
          <td><button class="btn btn-default"
                  type="button"
                  v-bind:aria-label="T.wordsEdit"
                  v-on:click.prevent="onEdit(problem)"><span aria-hidden="true"
                class="glyphicon glyphicon-pencil"></span></button></td>
          <td>{{problem.order}}</td>
          <td>
            <a v-bind:href="`/arena/problem/${problem.alias}/`">{{problem.alias}}</a>
          </td>
          <td>{{problem.points}}</td>
          <td><button class="close"
                  v-on:click="onRemove(problem)">×</button></td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import omegaup from '../../api.js';
import Autocomplete from '../Autocomplete.vue';
import problem_Versions from '../problem/Versions.vue';

const emptyCommit = {
  author: null,
  commit: '',
  commiter: null,
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
  @Prop() data!: omegaup.Problem[];

  T = T;
  UI = UI;
  alias = '';
  points = 100;
  order = this.data.length + 1;
  problems = this.data;
  selected: omegaup.Problem = { alias: '', order: 1, points: 100, title: '' };
  versionLog: omegaup.Commit[] = [];
  publishedRevision = emptyCommit;
  selectedRevision = emptyCommit;

  onSubmit(): void {
    this.$parent.$emit('add-problem', this);
  }

  onEdit(problem: omegaup.Problem): void {
    this.alias = problem.alias;
    this.points = problem.points;
    this.order = problem.order;
  }

  onRemove(problem: omegaup.Problem): void {
    this.selected = problem;
    this.$parent.$emit('remove-problem', this);
  }

  onRunsDiff(versions: omegaup.Commit[], selectedCommit: omegaup.Commit): void {
    let found = false;
    for (const problem of this.problems) {
      if (this.alias == problem.alias) {
        found = true;
        break;
      }
    }
    if (!found) {
      return;
    }
    this.$parent.$emit('runs-diff', this, versions, selectedCommit);
  }

  get addProblemButtonLabel(): string {
    for (const problem of this.problems) {
      if (this.alias == problem.alias) {
        return T.wordsUpdateProblem;
      }
    }
    return T.wordsAddProblem;
  }

  @Watch('problems')
  onProblemsChange(newValue: omegaup.Problem[]): void {
    this.alias = '';
    this.points = 100;
    this.order = newValue.length + 1;
  }

  @Watch('alias')
  onAliasChange(newProblemAlias: string) {
    if (!newProblemAlias) {
      this.versionLog = [];
      this.selectedRevision = this.publishedRevision = emptyCommit;
      return;
    }
    this.$parent.$emit('get-versions', newProblemAlias, this);
  }
}

</script>

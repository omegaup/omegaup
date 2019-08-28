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
          <label>{{T.contestAddproblemProblemPoints}}</label> <input class="form-control"
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

<script>
import {T, UI, API} from '../../omegaup.js';
import Autocomplete from '../Autocomplete.vue';
import problem_Versions from '../problem/Versions.vue';

export default {
  props: {
    data: Array,
    contestAlias: String,
  },
  data: function() {
    return {
      T: T,
      UI: UI,
      alias: '',
      points: 100,
      order: this.data.length + 1,
      problems: this.data,
      selected: {},
      versionLog: [],
      publishedRevision: null,
      selectedRevision: null,
    };
  },
  methods: {
    onSubmit: function() { this.$parent.$emit('add-problem', this);},
    onEdit: function(problem) {
      this.alias = problem.alias;
      this.points = problem.points;
      this.order = problem.order;
    },
    onRemove: function(problem) {
      this.selected = problem;
      this.$parent.$emit('remove-problem', this);
    },
    onRunsDiff: function(versions, selectedCommit) {
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
      API.Contest.runsDiff({
                   problem_alias: this.alias,
                   contest_alias: this.contestAlias,
                   version: selectedCommit.version,
                 })
          .then(function(response) {
            versions.$set(versions.runsDiff, selectedCommit.version,
                          response.diff);
          })
          .fail(UI.apiError);
    },
  },
  computed: {
    addProblemButtonLabel: function() {
      for (const problem of this.problems) {
        if (this.alias == problem.alias) {
          return T.wordsUpdateProblem;
        }
      }
      return T.wordsAddProblem;
    },
  },
  watch: {
    problems: function(val) {
      this.alias = '';
      this.points = 100;
      this.order = val.length + 1;
    },
    alias: function(problemAlias) {
      const self = this;
      if (!problemAlias) {
        self.versionLog = [];
        self.selectedRevision = self.publishedRevision = null;
        return;
      }
      API.Problem.versions({problem_alias: problemAlias})
          .then(function(result) {
            self.versionLog = result.log;
            let currentProblem = null;
            for (const problem of self.problems) {
              if (problem.alias == problemAlias) {
                currentProblem = problem;
                break;
              }
            }
            let publishedCommitHash = result.published;
            if (currentProblem != null) {
              publishedCommitHash = currentProblem.commit;
            }
            for (const revision of result.log) {
              if (publishedCommitHash == revision.commit) {
                self.selectedRevision = self.publishedRevision = revision;
                break;
              }
            }
          })
          .fail(function() {
            self.versionLog = [];
            self.selectedRevision = self.publishedRevision = null;
          });
    },
  },
  components: {
    'omegaup-autocomplete': Autocomplete,
    'omegaup-problem-versions': problem_Versions,
  },
};
</script>

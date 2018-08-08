<template>
  <div class="row">
    <div class="col-md-12 no-right-padding">
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileSolvedProblems }}</h2>
        </div>
        <table class="table table-striped"
               v-for="(problems, user) in groupedSolvedProblems">
          <thead>
            <tr>
              <th v-bind:colspan="columns">{{ user }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="groups in problems">
              <td v-for="problem in groups">
                <a v-bind:href="`/arena/problem/${problem.alias}/`">{{ problem.title }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!solvedProblems"><img src="/media/wait.gif"></div>
      </div>
      <div class="panel panel-default">
        <div class="panel-heading">
          <h2 class="panel-title">{{ T.profileUnsolvedProblems }}</h2>
        </div>
        <table class="table table-striped"
               v-for="(problems, user) in groupedUnsolvedProblems">
          <thead>
            <tr>
              <th v-bind:colspan="columns">{{ user }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="groups in problems">
              <td v-for="problem in groups">
                <a v-bind:href="`/arena/problem/${problem.alias}/`">{{ problem.title }}</a>
              </td>
            </tr>
          </tbody>
        </table>
        <div v-show="!unsolvedProblems"><img src="/media/wait.gif"></div>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    solvedProblems: undefined,
    unsolvedProblems: undefined,
  },
  computed: {
    groupedSolvedProblems: function() {
      return this.groupElements(this.solvedProblems, this.columns);
    },
    groupedUnsolvedProblems: function() {
      return this.groupElements(this.unsolvedProblems, this.columns);
    },
  },
  methods: {
    groupElements(elements, columns) {
      let groups = {};
      for (let user in elements) {
        groups[user] = [];
        for (let i = 0; i < elements[user].length; i += columns) {
          groups[user].push(elements[user].slice(i, i + columns));
        }
      }
      return groups;
    },
  },
  data: function() {
    return { T: T, columns: 3, }
  }
}
</script>

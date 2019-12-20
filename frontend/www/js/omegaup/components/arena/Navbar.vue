<template>
  <div class="navbar">
    <div class="problem-list">
      <div class="summary" v-bind:class="selectedSummary">
        <a class="name" href="#problems">{{ T.wordsSummary }}</a>
      </div>
      <div
        v-bind:class="problemClassName(problem.alias, problem.active)"
        v-for="problem in problemsList"
      >
        <a class="name" v-bind:href="problemLink(problem.alias)">{{
          problem.text
        }}</a>
        <span class="solved">{{ problem.score }}</span>
      </div>
    </div>
    <table class="mini-ranking" v-if="showRanking">
      <thead>
        <tr>
          <th></th>
          <th>{{ T.wordsUser }}</th>
          <th class="total" colspan="2">{{ T.wordsTotal }}</th>
          <th></th>
        </tr>
      </thead>
      <tbody class="user-list-template">
        <tr v-for="user in miniRanking">
          <td class="position">{{ user.position }}</td>
          <td class="user" v-html="user.username"></td>
          <td class="points">{{ user.points }}</td>
          <td class="penalty">{{ user.penalty }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style>
.problem-list > div {
  width: 19em;
  margin-bottom: 0.5em;
  background: #ddd;
  border: solid 1px #ccc;
  border-width: 1px 0 1px 1px;
  position: relative;
}

.problem-list > div a {
  color: #5588dd;
  display: block;
  padding: 0.5em;
  width: 100%;
  max-width: 212px;
}

.problem-list > div.active {
  background: white;
}

.problem-list > div.summary {
  margin-bottom: 1em;
}

.problem-list > div .solved {
  position: absolute;
  top: 0.5em;
  right: 1em;
}

.navbar .mini-ranking {
  width: 18em;
  margin-top: 2em;
}

.navbar .mini-ranking td {
  border: 1px solid #000;
  padding: 0.2em;
}

.navbar .mini-ranking th {
  padding: 0.2em;
}

.navbar .mini-ranking .position,
.navbar .mini-ranking .points,
.navbar .mini-ranking .penalty {
  text-align: center;
}

.navbar .mini-ranking .user,
.navbar .mini-ranking .user span {
  width: 10em;
  max-width: 10em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.navbar .mini-ranking .user span {
  display: block;
}

.navbar .mini-ranking td.points {
  border-right-style: dotted;
}

.navbar .mini-ranking td.penalty {
  border-left-width: 0;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';

@Component
export default class ArenaNavbar extends Vue {
  @Prop() showRanking!: boolean;
  @Prop() problemsList!: omegaup.ContestProblem[];
  @Prop() isSummarySelected!: boolean;
  @Prop() miniRanking!: omegaup.UserRank[];

  T = T;
  UI = UI;

  get selectedSummary(): string {
    return this.isSummarySelected ? 'active' : '';
  }

  problemClassName(problemAlias: string, isActive: boolean): string {
    if (!isActive) {
      return `problem_${problemAlias}`;
    }
    return `problem_${problemAlias} active`;
  }

  problemLink(problemAlias: string): string {
    return `#problems/${problemAlias}`;
  }
}
</script>

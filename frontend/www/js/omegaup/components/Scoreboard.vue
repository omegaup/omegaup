<template>
  <div class="omegaup-scoreboard">
    <div id="ranking-chart"></div>
    <table>
      <thead>
        <tr>
          <th></th>
          <th></th>
          <th>{{ T.wordsUser }}</th>
          <th v-for="(problem, index) in problems">
            <a v-bind:href="'#problems/' + problem.alias"
               v-bind:title="problem.alias">{{ String.fromCharCode(65 + index) }}</a>
          </th>
          <th v-bind:colspan="2 + problems.length">{{ T.wordsTotal }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(user, index) in ranking">
          <td class="legend" v-bind:style="{ backgroundColor: legendColor(index) }"></td>
          <td class="position">{{ user.place }}</td>
          <td>{{ renderUser(user) }} <img v-if="user.country" width="16" height="11" v-bind:title="user.country" v-bind:src="'/media/flags/' + user.country.toLowerCase() + '.png'" /></td>
          <td v-for="problem in user.problems" v-bind:class="problemClass(problem)">
            <template v-if="problem.runs > 0">
              <div class="points">{{ renderPoints(problem) }}</div>
              <div class="penalty"><span v-if="showPenalty">{{ problem.penalty }} </span>({{ problem.runs }})</div>
            </template>
            <template v-else>-</template>
          </td>
          <td>
            <div class="points">{{ user.total.points }}</div>
            <div class="penalty">{{ user.total.penalty }} ({{ totalRuns(user) }})</div>
          </td>
        </tr>
      </tbody>
    </table>
    <div class="footer">{{ lastUpdatedString }}</div>
  </div>
</template>

<script>
export default {
  props: {
    T: Object,
		scoreboardColors: Array,
    problems: Array,
    ranking: Array,
    lastUpdated: Date,
    showPenalty: {
      type: Boolean,
      default: true,
    },
  },
  data: function() {
    return {
    };
  },
  computed: {
    lastUpdatedString: function() {
      if (!this.lastUpdated) {
        return "";
      }
      return this.lastUpdated.toString();
    },
  },
  methods: {
		legendColor: function(idx) {
			if (idx > this.scoreboardColors.length) {
				return "";
			}
			return this.scoreboardColors[idx];
		},
    renderUser: function(u) {
      return u.username + (u.name && u.name != u.username ? ' (' + u.name + ')' : '');
    },
    renderPoints: function(p) {
      return (p.points > 0 ? '+' : '') + p.points;
    },
    totalRuns: function(u) {
      return u.problems.reduce((acc, val) => acc + val.runs, 0);
    },
    problemClass: function(p) {
      if (p.percent == 100) {
        return 'accepted';
      } else if (p.pending) {
        return 'pending';
      } else if (p.percent == 0 && p.runs > 0) {
        return 'wrong';
      } else {
        return '';
      }
    },
  },
};
</script>

<style>
.omegaup-scoreboard {
  max-width: 900px;
  margin: 0 auto;
}
.omegaup-scoreboard a {
  color: #5588DD;
}
.omegaup-scoreboard .footer {
  padding: 1em;
  text-align: right;
  font-size: 70%;
  color: grey;
}

.omegaup-scoreboard table {
  border-collapse: collapse;
  width: 100%;
}
.omegaup-scoreboard th {
  padding: 0.2em;
  text-align: center;
}
.omegaup-scoreboard td {
  text-align: center;
  vertical-align: middle;
  border: 1px solid #000;
  padding: 0.2em;
}
.omegaup-scoreboard td.accepted {
  background: #dfd;
}
.omegaup-scoreboard td.pending {
  background: #ddf;
}
.omegaup-scoreboard td.wrong {
  background: #fdd;
}
.omegaup-scoreboard td.position.recent-event {
  font-weight: bold;
  background: #dfd;
}
.omegaup-scoreboard td.accepted.recent-event {
  background: #8f8;
}
.omegaup-scoreboard td .points {
  font-weight: bold;
}
.omegaup-scoreboard td .penalty {
  font-size: 70%;
}
.omegaup-scoreboard td.position {
  width: 3.5em;
}
.omegaup-scoreboard td.legend {
  width: .5em;
}
.omegaup-scoreboard td[class$='points'] {
  width: 3.5em;
  border-right-style: dotted;
}
.omegaup-scoreboard td[class$='penalty'] {
  border-left-width: 0;
}
</style>

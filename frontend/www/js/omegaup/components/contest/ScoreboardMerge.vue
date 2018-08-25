<template>
  <div>
    <div class="post">
      <legend>Concurso: <select class="contests"
              multiple="multiple"
              size="10"
              v-model="selectedContests">
        <option v-bind:value="contest.alias"
                v-for="contest in availableContests">
          {{UI.contestTitle(contest)}}
        </option>
      </select></legend> <button class="btn"
           type="button"
           v-on:click.prevent="displayTable">Ver scoreboard total</button>
    </div>
    <div class="post">
      <table class="merged-scoreboard"
             v-if="scoreboard.length &gt; 0">
        <tr>
          <td></td>
          <td><strong>{{ T.User }}</strong></td>
          <td colspan="2"
              v-for="alias in aliases"><strong>{{ alias }}</strong></td>
          <td colspan="2"><strong>{{ T.wordsTotal }}</strong></td>
        </tr>
        <tr v-for="rank in scoreboard">
          <td><strong>{{ rank.place }}</strong></td>
          <td>
            <div class="username">
              {{ rank.username }}
            </div>
            <div class="name">
              {{ rank.username != rank.name ? rank.name : ' ' }}
            </div>
          </td>
          <td class="numeric"
              colspan="2"
              v-for="alias in aliases">{{ rank.contests[alias].points }}<span class=
              "scoreboard-penalty"
                v-if="showPenalty">({{ rank.contests[alias].penalty }})</span></td>
          <td class="numeric"
              colspan="2">{{ rank.totalPoints }}<span class="scoreboard-penalty"
                v-if="showPenalty">({{ rank.totalPenalty }})</span></td>
        </tr>
      </table>
      <table class="merged-scoreboard"
             v-else="">
        <tr>
          <td></td>
          <td><strong>{{ T.User }}</strong></td>
          <td colspan="2"><strong>{{ T.wordsTotal }}</strong></td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script>
import {T, UI} from '../../omegaup.js';

export default {
  props: {
    availableContests: Array,
    scoreboard: Array,
    showPenalty: Number,
    aliases: Array,
  },
  methods: {
    displayTable: function() {
      this.$emit('get-scoreboard', this.selectedContests);
    }
  },
  data: function() {
    return { T: T, selectedContests:[], UI: UI, }
  }
}
</script>

<style>
  .merged-scoreboard {
    background: white;
  }

  .merged-scoreboard td {
    text-align: center;
  }

  .scoreboard-penalty {
    padding-left: .5em;
    opacity: .7;
    color: red;
  }
</style>

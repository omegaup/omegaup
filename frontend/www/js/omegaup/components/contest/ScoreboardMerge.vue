<template>
  <div>
    <div class="post">
      <div class="copy">
        <legend>Concurso: <select class="contests"
                v-model="selectedContests"
                multiple="multiple"
                size="10">
          <option v-bind:value="contest.alias"
                  v-for="contest in availableContests">
            {{contest.title}}
          </option>
        </select></legend>
      </div>
      <div class="POS Boton"
           v-on:click.prevent="displayTable">
        Ver scoreboard total
      </div>
    </div>
    <table class="merged-scoreboard"
          v-if="scoreboard.length > 0">
      <tr>
        <td></td>
        <td><strong>{{ T.User }}</strong></td>
        <td colspan="2"
            v-for="alias in aliases"
        ><strong>{{ alias }}</strong></td>
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
            v-for="alias in aliases">({{ rank.contests[alias].points }}<span class=
            "scoreboard-penalty" v-if="showPenalty"> {{ rank.contests[alias].penalty }}</span>)</td>
        <td class="numeric"
            colspan="2">({{ rank.totalPoints }}<span class="scoreboard-penalty" v-if="showPenalty"> {{ rank.totalPenalty }}</span>)</td>
      </tr>
    </table>
    <table v-else class="merged-scoreboard">
      <tr>
        <td></td>
        <td><strong>{{ T.User }}</strong></td>
        <td colspan="2"><strong>{{ T.wordsTotal }}</strong></td>
      </tr>
    </table>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

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
    return {
      T: T,
      selectedContests: [],
    }
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

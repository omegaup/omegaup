<template>
  <div>
    <div class="post">
      <div class="copy">
        <legend>Concurso: <select class="contests"
                id='contests'
                multiple="multiple"
                name='contests'
                size="10">
          <option v-bind:value="contest.alias"
                  v-for="contest in contests">
            {{contest.title}}
          </option>
        </select></legend>
      </div>
      <div class="POS Boton"
           id="get-merged-scoreboard"
           v-on:click.prevent="displayTable">
        Ver scoreboard total
      </div>
    </div>
    <div class="post">
      <div class="copy"
           id="ranking"
           v-if="showTable">
        <table class="merged-scoreboard">
          <tr>
            <td></td>
            <td><strong>Username</strong></td>
            <td colspan="2"
                v-for="alias in aliases"
                v-if="isMoreThanZero"><strong>{{ alias }}</strong></td>
            <td colspan="2"><strong>{{ total }}</strong></td>
          </tr>
          <tr v-for="rank in scoreboard"
              v-if="scoreboard">
            <td><strong>{{ rank.place }}</strong></td>
            <td>
              <div class="username">
                {{ rank.username }}
              </div>
              <div class="name">
                {{ rank.username != rank.name ? rank.name : '&nbsp;' }}
              </div>
            </td>
            <td class="numeric"
                colspan="2"
                v-for="alias in aliases">({{ rank.contests[alias].points }}<span class=
                "scoreboard-penalty"
                  v-if="showPenalty">{{ ' ' + rank.contests[alias].penalty }}</span>)</td>
            <td class="numeric"
                colspan="2">({{ rank.totalPoints }}<span class="scoreboard-penalty"
                  v-if="showPenalty">{{ ' ' + rank.totalPenalty }}</span>)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    contests: Array,
    showTable: Boolean,
    isMoreThanZero: Boolean,
    scoreboard: Array,
    showPenalty: Boolean,
    aliases: Array,
  },
  methods: {
    displayTable: function() {
      let self = this;
      let contestAliases = $('select.contests option:selected')
                               .map(function() { return this.value })
                               .get();
      this.$emit('get-scoreboard', contestAliases);
    }
  },
  data: function() {
    return { total: T.wordsTotal, }
  }
}
</script>

<style>
  .merged-scoreboard td {
    text-align: center;
  }

  .scoreboard-penalty {
    padding-left: .5em;
    opacity: .7;
    color: red;
  }
</style>

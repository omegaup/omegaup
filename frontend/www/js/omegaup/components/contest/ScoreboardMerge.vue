<template>
  <div>
    <div class="post">
	    <div class="copy">
		    <legend>Concurso:
          <select class="contests" name='contests' id='contests' multiple="multiple" size="10">
            <option v-for="contest in contests"
                    v-bind:value="contest.alias"
            >{{contest.title}}</option>
          </select>
        </legend>
	    </div>
	    <div class="POS Boton" id="get-merged-scoreboard" v-on:click.prevent="displayTable">Ver scoreboard total</div>
    </div>

    <div class="post">
	    <div class="copy" id="ranking" v-if="showTable">
        <table class="merged-scoreboard">
          <tr>
            <td></td>
            <td><b>Username</b></td>
            <td v-if="isMoreThanZero"
                v-for="alias in aliases"
                colspan="2"><b>{{ alias }}</b>
            </td>
            <td colspan="2"><b>{{ total }}</b></td>
            <tr v-if="scoreboard" v-for="rank in scoreboard">
              <td><strong>{{ rank.place }}</strong></td>
              <td>
                <div class="username">{{ rank.username }}</div>
                <div class="name">{{ rank.username != rank.name ? rank.name : '&nbsp;' }}</div>
              </td>
              <td class="numeric"
                  v-for="alias in aliases"
                  colspan="2">({{ rank.contests[alias].points }}<span class="scoreboard-penalty" >{{ ' ' + rank.contests[alias].penalty }}</span>)
              </td>
              <td class="numeric" colspan="2">({{ rank.totalPoints }}<span class="scoreboard-penalty" >{{ ' ' + rank.totalPenalty }}</span>)
              </td>
            </tr>
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
    return {
      total: T.wordsTotal,
    }
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

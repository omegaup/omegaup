<template>
  <div>
    <div class="post">
      <legend>
        {{ T.wordsContest }}:
        <select
          v-model="selectedContests"
          class="contests"
          multiple="multiple"
          size="10"
        >
          <option v-for="contest in availableContests" :value="contest.alias">
            {{ ui.contestTitle(contest) }}
          </option>
        </select>
      </legend>
      <button class="btn" type="button" @click.prevent="onDisplayTable">
        {{ T.showTotalScoreboard }}
      </button>
    </div>
    <div class="post">
      <table v-if="scoreboard.length &gt; 0" class="merged-scoreboard">
        <tr>
          <td></td>
          <td>
            <strong>{{ T.username }}</strong>
          </td>
          <td v-for="alias in aliases" colspan="2">
            <strong>{{ alias }}</strong>
          </td>
          <td colspan="2">
            <strong>{{ T.wordsTotal }}</strong>
          </td>
        </tr>
        <tr v-for="rank in scoreboard">
          <td>
            <strong>{{ rank.place }}</strong>
          </td>
          <td>
            <div class="username">
              {{ rank.username }}
            </div>
            <div class="name">
              {{ rank.username != rank.name ? rank.name : ' ' }}
            </div>
          </td>
          <td v-for="alias in aliases" class="numeric" colspan="2">
            {{ rank.contests[alias].points
            }}<span v-if="showPenalty" class="scoreboard-penalty"
              >({{ rank.contests[alias].penalty }})</span
            >
          </td>
          <td class="numeric" colspan="2">
            {{ rank.totalPoints
            }}<span v-if="showPenalty" class="scoreboard-penalty"
              >({{ rank.totalPenalty }})</span
            >
          </td>
        </tr>
      </table>

      <table v-else class="merged-scoreboard">
        <tr>
          <td></td>
          <td>
            <strong>{{ T.username }}</strong>
          </td>
          <td colspan="2">
            <strong>{{ T.wordsTotal }}</strong>
          </td>
        </tr>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Emit } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class ScoreboardMerge extends Vue {
  @Prop() availableContests!: omegaup.Contest[];
  @Prop() scoreboard!: omegaup.Scoreboard[];
  @Prop() showPenalty!: boolean;
  @Prop() aliases!: Array<string>;

  T = T;
  ui = ui;
  selectedContests: Array<string> = [];

  @Emit('get-scoreboard')
  onDisplayTable(): Array<string> {
    return this.selectedContests;
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
  padding-left: 0.5em;
  opacity: 0.7;
  color: red;
}

.post {
  overflow-x: scroll;
}
</style>

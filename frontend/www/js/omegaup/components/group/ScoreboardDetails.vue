<template>
  <div class="card">
    <h3 class="card-header">
      {{ scoreboard.name }}
    </h3>
    <div class="card-body">
      <table class="table table-striped text-center">
        <thead>
          <tr>
            <th></th>
            <th></th>
            <th>{{ T.groupScoreboardDetailsCoder }}</th>
            <th v-for="(contest, index) in contests" :key="contest.alias">
              <a :href="`/arena/${contest.alias}`" :title="contest.title">{{
                index
              }}</a>
            </th>
            <th class="total" colspan="2">{{ T.wordsTotal }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(rank, index) in ranking" :key="rank.username">
            <td class="position">{{ index + 1 }}</td>
            <td class="legend"></td>
            <td class="user">
              {{ rank.username }}
              {{ rank.name == rank.username ? '' : `(${rank.name})` }}
            </td>
            <td
              v-for="contest in contests"
              :key="`prob_${contest.alias}_points`"
            >
              <div class="points">
                {{
                  rank.contests[contest.alias].points
                    ? `+${rank.contests[contest.alias].points}`
                    : '0'
                }}
              </div>
              <div class="penalty">
                {{ rank.contests[contest.alias].penalty }}
              </div>
            </td>
            <td class="points">
              <div class="points">{{ rank.total.points }}</div>
              <div class="penalty">{{ rank.total.penalty }}</div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../../lang';
import { types } from '../../api_types';

export default defineComponent({
  name: 'GroupScoreboardDetails',
  props: {
    ranking: {
      type: Array as PropType<types.ScoreboardRanking[]>,
      required: true,
    },
    scoreboard: {
      type: Object as PropType<types.ScoreboardDetails>,
      required: true,
    },
    contests: {
      type: Array as PropType<types.ScoreboardContest[]>,
      required: true,
    },
    scoreboardAlias: {
      type: String,
      required: true,
    },
    groupAlias: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      T,
    };
  },
});
</script>

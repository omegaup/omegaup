<template>
  <div class="card">
    <h4 class="card-header">{{ T.contestEditAdministrativeLinks }}</h4>
    <div class="card-body">
      <div class="list-group w-50 mx-auto">
        <a class="list-group-item" :href="`/arena/${contest.alias}/admin/`">{{
          T.wordsSubmissions
        }}</a>
        <a class="list-group-item" :href="`/contest/${contest.alias}/stats/`">{{
          T.profileStatistics
        }}</a>
        <a
          class="list-group-item"
          :href="`/contest/${contest.alias}/activity/`"
          >{{ T.activityReport }}</a
        >
        <a class="list-group-item" :href="`/arena/${contest.alias}/print/`">{{
          T.contestPrintableVersion
        }}</a>
        <a
          class="list-group-item"
          :href="`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url}`"
          >{{ T.contestScoreboardLink }}</a
        >
        <a
          class="list-group-item"
          :href="`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url_admin}`"
          >{{ T.contestScoreboardAdminLink }}</a
        >
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class Links extends Vue {
  @Prop() data!: types.Contest;

  T = T;
  contest = this.data;
}
</script>

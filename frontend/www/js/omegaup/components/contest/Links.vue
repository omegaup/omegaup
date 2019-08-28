<template>
  <div class="panel panel-primary">
    <div class="panel-body">
      <h3>{{T.contestEditAdministrativeLinks}}</h3>
      <table class="table table-striped">
        <thead></thead>
        <tbody class="contest-admin-links">
          <tr>
            <td>
              <a v-bind:href="`/arena/${contest.alias}/admin/`">{{T.wordsSubmissions}}</a>
            </td>
            <td>
              <a v-bind:href="`/contest/${contest.alias}/stats/`">{{T.profileStatistics}}</a>
            </td>
            <td>
              <a v-bind:href="`/contest/${contest.alias}/activity/`">{{T.wordsActivityReport}}</a>
            </td>
            <td>
              <a v-bind:href="`/arena/${contest.alias}/print/`">{{T.contestPrintableVersion}}</a>
            </td>
            <td>
              <a v-bind:href=
              "`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url}`">{{T.contestScoreboardLink}}</a>
            </td>
            <td>
              <a v-bind:href=
              "`/arena/${contest.alias}/scoreboard/${contest.scoreboard_url_admin}`">{{T.contestScoreboardAdminLink}}</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { T } from '../../omegaup.js';
import omegaup from '../../api.js';

@Component({})
export default class Links extends Vue {
  @Prop() data!: omegaup.Contest;

  T = T;
  contest = this.data;
}

</script>

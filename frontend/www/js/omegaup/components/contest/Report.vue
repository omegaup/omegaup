<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      {{ UI.formatString(T.contestReport, {contest_alias: contestAlias}) }}
    </div>
    <div class="panel-body">
      <div v-for="contestantData in contestReport">
        <h1>{{ T.username }}: {{ contestantData.username }}</h1>
        <h3>{{ T.wordsTotal }}:<span v-if=
        "contestantData.hasOwnProperty('total') &amp;&amp; contestantData.total.hasOwnProperty('points')">{{
        contestantData.total.points }}</span><span v-else="">0</span></h3>
        <div v-for="item in contestantData.problems">
          <h3>{{ T.wordsProblem }}: {{ item.alias }}</h3>
          <h3>{{ T.wordsPoints }}: {{ item.points }}</h3>
          <div v-if="item.run_details">
            <template v-for="group in item.run_details.details.groups">
              <table v-if=
              "item.run_details &amp;&amp; (((item || {}).run_details || {} ).details || {} ).groups">
                <tr>
                  <th>{{ T.wordsCase }}</th>
                  <th>{{ T.wordsTimeInSeconds }}</th>
                  <th>{{ T.wordsWallTimeInSeconds }}</th>
                  <th>{{ T.wordsMemory }} (MiB)</th>
                  <th>{{ T.wordsStatus }}</th>
                  <th>{{ T.rankScore }}</th>
                  <th>{{ T.wordsDifference }}</th>
                </tr>
                <tr v-for="temp in group.cases">
                  <td>{{ group.group }}.{{ temp.name }}</td>
                  <td class="numeric">{{ (temp.meta.time).toFixed(3) }}</td>
                  <td class="numeric">{{ (temp.meta.wall_time).toFixed(3) }}</td>
                  <td class="numeric">{{ (temp.meta.memory).toFixed(2) }}</td>
                  <td>{{ temp.verdict }}</td>
                  <td>{{ temp.score }}</td>
                  <td>
                    <template v-if="temp.out_diff">
                      {{ temp.out_diff }}
                    </template>
                  </td>
                </tr>
              </table>
              <table v-if=
              "item.run_details &amp;&amp; (((item || {}).run_details || {} ).details || {} ).groups">
                <tr>
                  <th>{{ T.wordsGroup }}</th>
                  <th>{{ T.rankScore }}</th>
                </tr>
                <tr v-for="group in item.run_details.details.groups">
                  <td>{{ group.group }}</td>
                  <td>{{ group.score }}</td>
                </tr>
              </table><br>
            </template>
          </div>
        </div>
        <hr>
      </div>
    </div>
    <div class="page-break"></div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

import { T } from '../../omegaup.js';
import omegaup from '../../api.js';
import UI from '../../ui.js';

interface ContestReport {
  country?: string;
  username: string;
  name?: string;
  problems: omegaup.Problem[];
  is_invited: boolean;
  total: Total;
}

interface Total {
  penalty: number;
  points: number;
}

@Component({})
export default class Report extends Vue {
  @Prop() contestReport!: ContestReport;
  @Prop() contestAlias!: string;

  T = T;
  UI = UI;
}

</script>

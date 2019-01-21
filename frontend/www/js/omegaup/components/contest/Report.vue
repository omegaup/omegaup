<template>
  <div>
    <div v-for="contestantData in contestReport">
      <h1>Username: {{ contestantData.username }}</h1>
      <h1>Total:<span v-if=
      "contestantData.hasOwnProperty('total') &amp;&amp; contestantData.total.hasOwnProperty('points')">{{
      contestantData.total.points }}</span><span v-else="">0</span></h1>
      <div v-for="(item,key) in contestantData.problems">
        <h4>Problem: {{ key }}</h4>
        <h4>Points: {{ item.points }}</h4>
        <template v-for="group in item.run_details.details.groups"></template>
        <table v-if=
        "item.run_details &amp;&amp; (((item || {}).run_details || {} ).details || {} ).groups">
          <tr>
            <th>Case</th>
            <th>Time (Sec)</th>
            <th>Time-wall (Sec)</th>
            <th>Memory (MiB)</th>
            <th>Status</th>
            <th>Score</th>
            <th>Diff</th>
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
            <th>Group</th>
            <th>Score</th>
          </tr>
          <tr v-for="group in item.run_details.details.groups">
            <td>{{ group.group }}</td>
            <td>{{ group.score }}</td>
          </tr>
        </table><br>
      </div>
    </div>
    <hr>
    <div class="page-break"></div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';

export default {
  props: {
    contestReport: Array,
  },
  mounted: function() { console.log(this.contestReport)}
};

</script>

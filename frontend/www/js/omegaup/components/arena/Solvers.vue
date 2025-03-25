<template>
  <table>
    <caption>
      {{
        T.wordsBestSolvers
      }}
    </caption>
    <thead>
      <tr>
        <th>{{ T.contestParticipant }}</th>
        <th>{{ T.wordsLanguage }}</th>
        <th>{{ T.wordsMemory }}</th>
        <th>{{ T.wordsRuntime }}</th>
        <th>{{ T.wordsTime }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="solver in solvers" :key="solver.username">
        <td data-submission-user>
          <omegaup-username
            :classname="solver.classname"
            :username="solver.username"
            :linkify="true"
          ></omegaup-username>
        </td>
        <td data-submission-language>{{ solver.language }}</td>
        <td data-submission-memory>
          {{ (solver.memory / (1024 * 1024)).toFixed(2) }}
        </td>
        <td data-submission-runtime>
          {{ (solver.runtime / 1000.0).toFixed(2) }}
        </td>
        <td data-submission-time>{{ time.formatTimestamp(solver.time) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as time from '../../time';
import omegaup_Username from '../user/Username.vue';

@Component({
  components: {
    'omegaup-username': omegaup_Username,
  },
})
export default class Solvers extends Vue {
  @Prop() solvers!: types.BestSolvers[];

  T = T;
  time = time;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
table {
  width: 100%;
  border: 1px solid var(--arena-solvers-table-border-color);
  margin-top: 2em;
}

caption {
  caption-side: top;
  font-weight: bold;
  font-size: 1em;
  margin-bottom: 1em;
}

td,
th {
  border: 1px solid var(--arena-solvers-td-border-color);
  border-width: 1px 0;
  text-align: center;
}
</style>

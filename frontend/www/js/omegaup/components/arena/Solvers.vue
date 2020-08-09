<template>
  <table data-best-solvers>
    <caption>
      {{
        T.wordsBestSolvers
      }}
    </caption>
    <thead>
      <tr>
        <th>{{ T.wordsUser }}</th>
        <th>{{ T.wordsLanguage }}</th>
        <th>{{ T.wordsMemory }}</th>
        <th>{{ T.wordsRuntime }}</th>
        <th>{{ T.wordsTime }}</th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="solver in solvers" v-bind:key="solver.username">
        <td>
          <omegaup-username
            v-bind:classname="solver.classname"
            v-bind:username="solver.username"
            v-bind:linkify="true"
          ></omegaup-username>
        </td>
        <td>{{ solver.language }}</td>
        <td>{{ (solver.runtime / 1000.0).toFixed(2) }}</td>
        <td>{{ (solver.memory / (1024 * 1024)).toFixed(2) }}</td>
        <td>{{ time.formatTimestamp(solver.time) }}</td>
      </tr>
    </tbody>
  </table>
</template>

<style lang="scss" scoped>
caption {
  caption-side: top;
}

[data-best-solvers] {
  width: 100%;
  border: 1px solid #ccc;
  margin-top: 2em;
}

[data-best-solvers] caption {
  font-weight: bold;
  font-size: 1em;
  margin-bottom: 1em;
}

[data-best-solvers] td,
[data-best-solvers] th {
  border: 1px solid #ccc;
  border-width: 1px 0;
  text-align: center;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit } from 'vue-property-decorator';
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

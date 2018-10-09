<template>
  <div class="panel panel-default no-bottom-margin">
    <div class="panel-heading">
      <h3 class="panel-title">My submissions</h3>
    </div>
    <div class="panel-body">
      <omegaup-run-table></omegaup-run-table>
    </div>
  </div>
</template>

<script>
import RunTable from './arena/RunTable.vue';
import {T} from '../omegaup.js';
export default {
  props: {runs: Array},
  components: {'omegaup-run-table': RunTable}
}

</script>

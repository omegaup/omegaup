<template>
  <div class="post">
    <div class="copy">
      <h1>Estadísticas en vivo</h1>
      <div class="total-runs">
        <h2></h2>
      </div><verdict-chart v-bind:contestalias="contestAlias"
           v-bind:stats="stats"></verdict-chart> <distribution-chart v-bind:contestalias=
           "contestAlias"
           v-bind:stats="stats"></distribution-chart>
      <div class="pending-runs-chart"></div>
    </div>
  </div>
</template>

<script>
import verdict_chart from '../VerdictChart.vue';
import distribution_chart from '../DistributionChart.vue';
export default {
  props: {
    stats: Object,
    contestAlias: String,
  },
  components: {
    'verdict-chart': verdict_chart,
    'distribution-chart': distribution_chart,
  },
};

</script>

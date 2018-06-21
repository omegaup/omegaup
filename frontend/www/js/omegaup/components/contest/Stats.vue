<template>
  <div class="post">
    <div class="copy">
      <h1>Estadísticas en vivo</h1>
      <div class="total-runs">
        <h2></h2>
      </div><!-- <div class="verdict-chart"></div> -->
      <verdict-chart></verdict-chart>
      <div class="distribution-chart"></div>
      <div class="pending-runs-chart"></div>
    </div>
  </div>
</template>

<script>
import verdict_chart from '../VerdictChart.vue';
export default {
  props: {
    stats: Object,
    contestAlias: String,
  },
  components: {
    'verdict-chart': verdict_chart,
  },
};

</script>

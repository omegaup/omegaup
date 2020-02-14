<template>
  <div>
    <highcharts v-bind:options="chartOptions"></highcharts>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { Chart } from 'highcharts-vue';

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class Navbar extends Vue {
  @Prop() chartOptions!: Chart;
}
</script>

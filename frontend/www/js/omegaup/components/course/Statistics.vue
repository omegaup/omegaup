<template>
  <div class="container-fluid">
    <h2 class="text-center">
        <a v-bind:href="courseUrl">{{ course.name }}</a>
    </h2>
    <highcharts></highcharts>
  </div>
  <!-- panel -->
</template>


<script>
import { Chart } from 'highcharts-vue';

@Component({
  components: {
    highcharts: Chart,
  },
})
export default class Statistics extends Vue {

}
</script>
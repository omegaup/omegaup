<template>
  <div class="container-fluid">
    <h2 class="text-center">
      <a v-bind:href="courseUrl">{{ course.name }}</a>
    </h2>
    <br />
    <div>
      <div class="d-flex justify-content-center">
        <select v-model="selected" class="text-center">
          <option v-for="option in options" v-bind:value="option.value">{{
            option.text
          }}</option>
        </select>
      </div>
      <highcharts v-bind:options="selected"></highcharts>
    </div>
  </div>
  <!-- panel -->
</template>

<script lang="ts">
import { Chart } from 'highcharts-vue';
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
@Component({
  components: {
    highcharts: Chart,
  },
})
export default class Statistics extends Vue {
  @Prop() course!: types.CourseDetails;
  @Prop() varianceChartOptions!: Chart;
  @Prop() averageChartOptions!: Chart;
  @Prop() highScoreChartOptions!: Chart;
  @Prop() lowScoreChartOptions!: Chart;
  @Prop() maximumChartOptions!: Chart;
  @Prop() minimumChartOptions!: Chart;
  T = T;
  get courseUrl(): string {
    return `/course/${this.course.alias}/`;
  }
  data() {
    return {
      selected: this.varianceChartOptions,
      options: [
        { value: this.varianceChartOptions, text: T.wordsVariance },
        { value: this.averageChartOptions, text: T.wordsAverageScore },
        {
          value: this.highScoreChartOptions,
          text: `${T.wordsStudentsAbove} 60%`,
        },
        {
          value: this.lowScoreChartOptions,
          text: `${T.wordsStudentsScored} 0%`,
        },
        { value: this.minimumChartOptions, text: T.wordsMinimumScore },
        { value: this.maximumChartOptions, text: T.wordsMaximumScore },
      ],
    };
  }
}
</script>

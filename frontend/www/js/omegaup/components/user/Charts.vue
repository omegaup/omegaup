<template>
  <div class="panel-body">
    <label><input type="radio"
           v-model="type"
           v-on:click="onSelectType"
           value="delta"> {{ T.profileStatisticsDelta }}</label> <label><input type="radio"
           v-model="type"
           v-on:click="onSelectType"
           value="cumulative"> {{ T.profileStatisticsCumulative }}</label> <input id="total"
         type="radio"
         v-model="type"
         v-on:click="onSelectType"
         value="total"> <label for="total">{{ T.profileStatisticsTotal }}</label>
    <div class="period_group text-center"
         v-if="type != 'total'">
      <input id="day"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="day"> <label for="day">{{ T.profileStatisticsDay }}</label> <input id="week"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="week"> <label for="week">{{ T.profileStatisticsWeek }}</label> <input id="month"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="month"> <label for="month">{{ T.profileStatisticsMonth }}</label> <input id=
           "year"
           name="period"
           type="radio"
           v-model="period"
           v-on:click="onSelectPeriod"
           value="year"> <label for="year">{{ T.profileStatisticsYear }}</label>
    </div>
    <div id="verdict-chart"></div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import UI from '../../ui.js';

export default {
  props: {},
  data: function() { return {T: T, UI: UI, type: 'total', period: 'day'};},
  methods: {
    onSelectType: function() {
      this.$emit('select-type', this.type, this.period);
    },
    onSelectPeriod: function() {
      this.$emit('select-period', this.type, this.period);
    },
  },
  components: {},
};
</script>

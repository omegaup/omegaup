<template>
  <div class="card">
    <h2 class="card-header text-white bg-primary">Report stats</h2>
    <div class="card-body">
      <form>
        <div class="form-row">
          <div class="form-group col-md-6">
            <label for="report-stats-start-date" class="control-label">
              {{ T.wordsStartTime }}
            </label>
            <omegaup-datepicker
              v-model="selectedStartTime"
            ></omegaup-datepicker>
          </div>
          <div class="form-group col-md-6">
            <label for="report-stats-end-date" class="control-label">
              {{ T.wordsEndTime }}
            </label>
            <omegaup-datepicker
              v-model="selectedEndTime"
              :max="new Date()"
            ></omegaup-datepicker>
          </div>
          <div class="form-group">
            <button
              class="btn btn-primary"
              @click.prevent="
                $emit('update-report', {
                  startTime: selectedStartTime,
                  endTime: selectedEndTime,
                })
              "
            >
              {{ T.reportStatsGenerate }}
            </button>
          </div>
        </div>
      </form>
      <pre>
        <code class=" w-100">{{ formattedReport }}</code>
      </pre>

      <button
        v-clipboard="() => formattedReport"
        class="btn btn-outline-primary"
        name="copy"
        type="button"
        data-copy-to-clipboard
        :aria-label="T.wordsCopyToClipboard"
        :title="T.wordsCopyToClipboard"
        @click.prevent="ui.success(T.reportStatsCopiedToClipboard)"
      >
        <font-awesome-icon icon="clipboard" />
      </button>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import Clipboard from 'v-clipboard';
import DatePicker from '../DatePicker.vue';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';

import { js as beautify } from 'js-beautify';
library.add(fas);
Vue.use(Clipboard);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-datepicker': DatePicker,
  },
})
export default class AdminReportStats extends Vue {
  @Prop() report!: types.ReportStatsPayload;
  @Prop() startTime!: Date;
  @Prop() endTime!: Date;

  T = T;
  ui = ui;

  selectedStartTime: Date = this.startTime;
  selectedEndTime: Date = this.endTime;

  get formattedReport(): string {
    return beautify(JSON.stringify(this.report), { indent_size: 2 });
  }
}
</script>

<style scoped lang="scss">
@import '../../../../sass/main.scss';
</style>

<template>
  <datetime
    v-model="stringValue"
    type="datetime"
    :format="LuxonDateTime.DATETIME_SHORT"
    input-class="form-control"
    minute-step="10"
    :max-datetime="finish ? time.formatDateTimeLocal(finish) : null"
    :min-datetime="start ? time.formatDateTimeLocal(start) : null"
  >
    <template #button-cancel>
      <font-awesome-icon :icon="['fas', 'times']" />
      {{ T.wordsCancel }}
    </template>
    <template #button-confirm>
      <font-awesome-icon :icon="['fas', 'check-circle']" />
      {{ T.wordsConfirm }}
    </template>
  </datetime>
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import T from '../lang';
import * as time from '../time';
import '../../../third_party/js/bootstrap-datetimepicker.min.js';
import '../../../third_party/js/locales/bootstrap-datetimepicker.es.js';
import '../../../third_party/js/locales/bootstrap-datetimepicker.pt-BR.js';
import { Datetime } from 'vue-datetime';
import { DateTime as LuxonDateTime } from 'luxon';
import 'vue-datetime/dist/vue-datetime.css';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { faTimes, faCheckCircle } from '@fortawesome/free-solid-svg-icons';
library.add(faTimes, faCheckCircle);

@Component({
  components: {
    datetime: Datetime,
    FontAwesomeIcon,
  },
})
export default class DateTimePicker extends Vue {
  T = T;
  time = time;
  LuxonDateTime = LuxonDateTime;

  @Prop() value!: Date;
  @Prop({ default: true }) enabled!: boolean;
  @Prop({ default: T.dateTimePickerFormat }) format!: string;
  @Prop({ default: null }) start!: Date;
  @Prop({ default: null }) finish!: Date;
  @Prop({ default: false }) readonly!: boolean;
  @Prop({ default: false }) isInvalid!: boolean;

  stringValue: string = time.formatDateTimeLocal(this.value);

  @Watch('stringValue')
  onStringValueChanged(newStringValue: string) {
    this.$emit('input', time.parseDateTimeLocal(newStringValue));
  }
}
</script>

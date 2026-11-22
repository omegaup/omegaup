<template>
  <input
    v-model="stringValue"
    class="form-control"
    :class="{ 'is-invalid': isInvalid }"
    required="required"
    size="16"
    type="datetime-local"
    :disabled="!enabled"
    :max="finish ? time.formatDateTimeLocal(finish) : null"
    :min="start ? time.formatDateTimeLocal(addDelay(start)) : null"
    :readonly="readonly || usedFallback"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../lang';
import * as time from '../time';
import '../../../third_party/js/bootstrap-datetimepicker.min.js';
import '../../../third_party/js/locales/bootstrap-datetimepicker.es.js';
import '../../../third_party/js/locales/bootstrap-datetimepicker.pt-BR.js';

export default defineComponent({
  name: 'DateTimePicker',
  props: {
    value: {
      type: Date,
      required: true,
    },
    enabled: {
      type: Boolean,
      default: true,
    },
    format: {
      type: String,
      default: T.dateTimePickerFormat,
    },
    start: {
      type: Date as PropType<Date | null>,
      default: null,
    },
    finish: {
      type: Date as PropType<Date | null>,
      default: null,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    isInvalid: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      T,
      time,
      usedFallback: false,
      stringValue: time.formatDateTimeLocal(this.value),
    };
  },
  watch: {
    stringValue(newStringValue: string) {
      if (this.usedFallback) {
        // If the fallback was used, we don't need to update anything.
        return;
      }
      this.$emit('input', time.parseDateTimeLocal(newStringValue));
    },
    value(newValue: Date) {
      this.stringValue = time.formatDateTimeLocal(newValue);
      if (this.usedFallback) {
        $(this.$el).data('datetimepicker').setDate(newValue);
      }
    },
  },
  mounted() {
    if ((this.$el as HTMLInputElement).type === 'text') {
      // Even though we declared the input as having datetime-local type,
      // browsers that don't support it will silently change the type to text.
      // In that case, use the bootstrap datetimepicker.
      this.mountedFallback();
    }
  },
  methods: {
    mountedFallback() {
      this.usedFallback = true;
      $(this.$el)
        .datetimepicker({
          format: this.format,
          defaultDate: this.value,
          locale: T.locale,
        })
        .on('change', () => {
          this.$emit('input', $(this.$el).data('datetimepicker').getDate());
        });

      $(this.$el).data('datetimepicker').setDate(this.value);
      if (this.start !== null) {
        $(this.$el).data('datetimepicker').setStartDate(this.start);
      }
      if (this.finish !== null) {
        $(this.$el).data('datetimepicker').setEndDate(this.finish);
      }
    },
    addDelay(date: Date) {
      // Since test field population is slow, it's necessary to add a delay
      // of a few minutes to prevent the test from failing due to
      // the next minute starting.
      let delayedDate = new Date(date);
      const delay = 5;
      delayedDate.setMinutes(delayedDate.getMinutes() - delay);
      return delayedDate;
    },
  },
});
</script>

<style>
@import '../../../third_party/css/bootstrap-datetimepicker.css';
</style>

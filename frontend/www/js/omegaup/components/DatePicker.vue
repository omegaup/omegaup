<template>
  <input
    v-model="stringValue"
    :name="name"
    :min="minDateStr"
    :max="maxDateStr"
    class="form-control"
    :class="{ 'is-invalid': isInvalid }"
    required="required"
    size="16"
    type="date"
    :disabled="!enabled"
    :readonly="usedFallback"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../lang';
import * as time from '../time';
import '../../../third_party/js/bootstrap-datepicker.js';

export default defineComponent({
  name: 'DatePicker',
  props: {
    name: {
      type: String,
      default: '',
    },
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
      default: T.datePickerFormat,
    },
    isInvalid: {
      type: Boolean,
      default: false,
    },
    min: {
      type: Date as PropType<Date | null>,
      default: null,
    },
    max: {
      type: Date as PropType<Date | null>,
      default: null,
    },
  },
  data() {
    return {
      T,
      usedFallback: false,
      stringValue: time.formatDateLocal(this.value),
    };
  },
  computed: {
    minDateStr(): string | undefined {
      return this.min?.toISOString()?.split('T')?.[0];
    },
    maxDateStr(): string | undefined {
      return this.max?.toISOString()?.split('T')?.[0];
    },
  },
  watch: {
    stringValue(newStringValue: string) {
      if (this.usedFallback) {
        // If the fallback was used, we don't need to update anything.
        return;
      }
      this.$emit('input', time.parseDateLocal(newStringValue));
    },
    value(newValue: Date) {
      this.stringValue = time.formatDateLocal(newValue);
      if (this.usedFallback) {
        $(this.$el).datepicker('setValue', newValue);
      }
    },
  },
  mounted() {
    if ((this.$el as HTMLInputElement).type === 'text') {
      // Even though we declared the input as having date type,
      // browsers that don't support it will silently change the type to text.
      // In that case, use the bootstrap datepicker.
      this.mountedFallback();
    }
  },
  methods: {
    mountedFallback() {
      this.usedFallback = true;
      $(this.$el)
        .datepicker({
          weekStart: 1,
          format: this.format,
        })
        .on('changeDate', (ev) => {
          this.$emit('input', ev.date);
        })
        .datepicker('setValue', this.value);
    },
  },
});
</script>

<style>
@import '../../../third_party/css/bootstrap-datepicker.css';
</style>

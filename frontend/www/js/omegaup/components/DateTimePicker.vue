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
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import T from '../lang';
import * as time from '../time';
import '../../../third_party/js/bootstrap-datetimepicker.min.js';
import '../../../third_party/js/locales/bootstrap-datetimepicker.es.js';
import '../../../third_party/js/locales/bootstrap-datetimepicker.pt-BR.js';

@Component
export default class DateTimePicker extends Vue {
  T = T;
  time = time;

  @Prop() value!: Date;
  @Prop({ default: true }) enabled!: boolean;
  @Prop({ default: T.dateTimePickerFormat }) format!: string;
  @Prop({ default: null }) start!: Date;
  @Prop({ default: null }) finish!: Date;
  @Prop({ default: false }) readonly!: boolean;
  @Prop({ default: false }) isInvalid!: boolean;

  private usedFallback: boolean = false;
  private stringValue: string = time.formatDateTimeLocal(this.value);

  public mounted() {
    if ((this.$el as HTMLInputElement).type === 'text') {
      // Even though we declared the input as having datetime-local type,
      // browsers that don't support it will silently change the type to text.
      // In that case, use the bootstrap datetimepicker.
      this.mountedFallback();
    }
  }

  private mountedFallback() {
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
  }

  private addDelay(date: Date) {
    // Since test field population is slow, it's necessary to add a delay
    // of a few minutes to prevent the test from failing due to
    // the next minute starting.
    let delayedDate = new Date(date);
    const delay = 5;
    delayedDate.setMinutes(delayedDate.getMinutes() - delay);
    return delayedDate;
  }

  @Watch('stringValue')
  onStringValueChanged(newStringValue: string) {
    if (this.usedFallback) {
      // If the fallback was used, we don't need to update anything.
      return;
    }
    this.$emit('input', time.parseDateTimeLocal(newStringValue));
  }

  @Watch('value')
  onPropertyChanged(newValue: Date) {
    this.stringValue = time.formatDateTimeLocal(newValue);
    if (this.usedFallback) {
      $(this.$el).data('datetimepicker').setDate(newValue);
    }
  }
}
</script>

<style>
@import '../../../third_party/css/bootstrap-datetimepicker.css';
</style>

<template>
  <input
    class="form-control"
    v-bind:class="{ 'is-invalid': isInvalid }"
    required="required"
    size="16"
    type="datetime-local"
    v-bind:disabled="!enabled"
    v-bind:max="finish ? time.formatDateTimeLocal(finish) : null"
    v-bind:min="start ? time.formatDateTimeLocal(start) : null"
    v-bind:readonly="readonly || usedFallback"
    v-model="stringValue"
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
    let self = this;
    self.usedFallback = true;
    $(self.$el)
      .datetimepicker({
        format: self.format,
        defaultDate: self.value,
        locale: T.locale,
      })
      .on('change', () => {
        self.$emit('input', $(self.$el).data('datetimepicker').getDate());
      });

    $(this.$el).data('datetimepicker').setDate(self.value);
    if (self.start !== null) {
      $(this.$el).data('datetimepicker').setStartDate(self.start);
    }
    if (self.finish !== null) {
      $(this.$el).data('datetimepicker').setEndDate(self.finish);
    }
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

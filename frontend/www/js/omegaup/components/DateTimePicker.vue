<template>
  <input class="form-control"
        readonly
        size="16"
        type="text"
        v-bind:disabled="!enabled">
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import { T } from '../omegaup.js';

@Component
export default class DateTimePicker extends Vue {
  T = T;

  @Prop() value!: Date;
  @Prop({ default: true }) enabled!: boolean;
  @Prop({ default: T.dateTimePickerFormat }) format!: string;
  @Prop({ default: null }) start!: Date;
  @Prop({ default: null }) finish!: Date;

  mounted() {
    let self = this;
    $(self.$el)
      .datetimepicker({
        format: self.format,
        defaultDate: self.value,
        locale: T.locale,
      })
      .on('change', ev => {
        self.$emit(
          'input',
          $(self.$el)
            .data('datetimepicker')
            .getDate(),
        );
      });

    $(this.$el)
      .data('datetimepicker')
      .setDate(self.value);
    if (self.start !== null) {
      $(this.$el)
        .data('datetimepicker')
        .setStartDate(self.start);
    }
    if (self.finish !== null) {
      $(this.$el)
        .data('datetimepicker')
        .setEndDate(self.finish);
    }
  }

  @Watch('value')
  onPropertyChanged(newValue: string) {
    $(this.$el)
      .data('datetimepicker')
      .setDate(newValue);
  }
}

</script>

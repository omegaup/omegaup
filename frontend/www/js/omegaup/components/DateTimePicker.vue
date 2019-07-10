<template>
  <input class="form-control"
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
  }

  @Watch('value')
  onPropertyChanged(newValue: string) {
    $(this.$el)
      .data('datetimepicker')
      .setDate(newValue);
  }
}

</script>

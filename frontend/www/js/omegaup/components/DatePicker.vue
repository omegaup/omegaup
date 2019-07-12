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
export default class DatePicker extends Vue {
  T = T;

  @Prop() value!: Date;
  @Prop({ default: true }) enabled!: boolean;
  @Prop({ default: T.datePickerFormat }) format!: string;

  mounted() {
    let self = this;
    $(self.$el)
      .datepicker({
        weekStart: 1,
        format: self.format,
      })
      .on('changeDate', ev => {
        self.$emit('input', ev.date);
      })
      .datepicker('setValue', self.value);
  }

  @Watch('value')
  onPropertyChanged(newValue: string) {
    $(this.$el).datepicker('setValue', newValue);
  }
}

</script>

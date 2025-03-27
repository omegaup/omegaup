<template>
  <b-input-group size="16">
    <!-- Editable Text Input -->
    <b-form-input
      v-model="stringValue"
      :name="name"
      :disabled="!enabled"
      placeholder="YYYY-MM-DD"
      :class="{ 'is-invalid': isInvalid }"
      required
      @change="onInputChange"
    />
    
    <!-- Calendar Input -->
    <b-form-datepicker
      v-model="stringValue"
      :min="minDateStr"
      :max="maxDateStr"
      button-only
      right
      :disabled="!enabled"
      @input="onDateSelected"
    />
  </b-input-group>
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import T from '../lang';
import * as time from '../time';
import { BFormDatepicker, BFormInput, BInputGroup } from 'bootstrap-vue';

@Component({
  components: { BFormDatepicker, BFormInput, BInputGroup },
})
export default class UpdatedDatePicker extends Vue {
  T = T;

  @Prop({ default: '' }) name!: string;
  @Prop() value!: Date;
  @Prop({ default: true }) enabled!: boolean;
  @Prop({ default: T.datePickerFormat }) format!: string;
  @Prop({ default: false }) isInvalid!: boolean;
  @Prop({ default: null }) min!: Date | null;
  @Prop({ default: null }) max!: Date | null;

  private stringValue: string = '';

  get minDateStr() {
    return this.min ? time.formatDateLocal(this.min) : '';
  }

  get maxDateStr() {
    return this.max ? time.formatDateLocal(this.max) : '';
  }

  mounted() {
    this.updateStringValue();
  }

  @Watch('value', { immediate: true })
  onValueChanged(newValue: Date) {
    if (newValue && time.formatDateLocal(newValue) !== this.stringValue) {
      this.stringValue = time.formatDateLocal(newValue);
    }
  }

  updateStringValue() {
    if (this.value && this.value instanceof Date && !isNaN(this.value.getTime())) {
      this.stringValue = time.formatDateLocal(this.value);
    } else {
      this.stringValue = '';
    }
  }

  onInputChange() {
    const parsedDate = time.parseDateLocal(this.stringValue);
    if (parsedDate) {
      this.$emit('input', parsedDate);
    }
  }

  onDateSelected(newDate: Date) {
    if (newDate) {
      this.$emit('input', newDate);
      this.stringValue = time.formatDateLocal(newDate);
    }
  }
}
</script>
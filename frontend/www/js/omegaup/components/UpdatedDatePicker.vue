<template>
  <b-input-group>
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
    <b-input-group-append>
      <b-form-datepicker
        v-model="stringValue"
        :min="minDateStr"
        :max="maxDateStr"
        button-only
        right
        :disabled="!enabled"
        :locale="locale"
        :label-help="''"
        @input="onDateSelected"
      />
    </b-input-group-append>

  </b-input-group>
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop } from 'vue-property-decorator';
import T from '../lang';
import * as time from '../time';
import { BFormDatepicker, BFormInput, BInputGroup, BInputGroupAppend } from 'bootstrap-vue';

@Component({
  components: { BFormDatepicker, BFormInput, BInputGroup, BInputGroupAppend },
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
  @Prop({ default: 'en' }) locale!: string;

  private stringValue: string | null = null;

  get minDateStr() {
    return this.min ? time.formatDateLocal(this.min) : null;
  }

  get maxDateStr() {
    return this.max ? time.formatDateLocal(this.max) : null;
  }

  mounted() {
    this.updateStringValue();
  }

  updateStringValue() {
    if (this.value && this.value instanceof Date && !isNaN(this.value.getTime())) {
      this.stringValue = time.formatDateLocal(this.value);
    } else {
      this.stringValue = '';
    }
  }

  @Watch('value', { immediate: true })
  onValueChanged(newValue: Date) {
    if (newValue && time.formatDateLocal(newValue) !== this.stringValue) {
      this.stringValue = time.formatDateLocal(newValue);
    }
  }

  onInputChange() {
    if (!this.stringValue) {
      return;
    }

    const parsedDate = time.parseDateLocal(this.stringValue);
    if (parsedDate) {
      this.$emit('input', parsedDate);
    }
  }

  onDateSelected(newDate: string) {
    this.$emit('input', time.parseDateLocal(newDate));
  }

}
</script>

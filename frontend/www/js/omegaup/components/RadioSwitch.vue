<template>
  <div class="form-control container-fluid" :readonly="readonly">
    <div class="form-check form-check-inline">
      <label class="form-check-label">
        <input
          v-model="radioValue"
          :disabled="readonly"
          class="form-check-input"
          type="radio"
          :name="name"
          :value="valueForTrue"
          @change.prevent="onUpdateInput"
        />{{ textForTrue }}
      </label>
    </div>
    <div class="form-check form-check-inline">
      <label class="form-check-label">
        <input
          v-model="radioValue"
          :disabled="readonly"
          class="form-check-input"
          type="radio"
          :name="name"
          :value="valueForFalse"
          @change.prevent="onUpdateInput"
        />{{ textForFalse }}
      </label>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import T from '../lang';

export default defineComponent({
  name: 'RadioSwitch',
  props: {
    name: {
      type: String,
      default: '',
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    selectedValue: {
      type: (null as unknown) as PropType<any>,
      default: undefined,
    },
    valueForTrue: {
      type: (null as unknown) as PropType<any>,
      default: true,
    },
    valueForFalse: {
      type: (null as unknown) as PropType<any>,
      default: false,
    },
    textForTrue: {
      type: String,
      default: T.wordsYes,
    },
    textForFalse: {
      type: String,
      default: T.wordsNo,
    },
  },
  data() {
    return {
      radioValue: this.selectedValue ?? this.valueForFalse,
    };
  },
  watch: {
    radioValue(newValue: any): void {
      this.$emit('update:value', newValue);
    },
  },
  methods: {
    onUpdateInput(newValue: any): void {
      this.$emit('update:value', newValue);
    },
  },
});
</script>

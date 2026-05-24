<template>
  <div class="form-control container-fluid" :readonly="readonly">
    <div class="form-check form-check-inline">
      <label class="form-check-label">
        <input
          :checked="currentValue === valueForTrue"
          :disabled="readonly"
          class="form-check-input"
          type="radio"
          :name="name"
          :value="valueForTrue"
          @change="onChange(valueForTrue)"
        />{{ textForTrue }}
      </label>
    </div>
    <div class="form-check form-check-inline">
      <label class="form-check-label">
        <input
          :checked="currentValue === valueForFalse"
          :disabled="readonly"
          class="form-check-input"
          type="radio"
          :name="name"
          :value="valueForFalse"
          @change="onChange(valueForFalse)"
        />{{ textForFalse }}
      </label>
    </div>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { Component, Prop } from 'vue-facing-decorator';
import T from '../lang';

@Component
export default class RadioSwitch extends Vue {
  @Prop() name!: string;
  @Prop({ default: false }) readonly!: boolean;
  @Prop() selectedValue!: any;
  @Prop({ default: true }) valueForTrue!: any;
  @Prop({ default: false }) valueForFalse!: any;
  @Prop({ default: T.wordsYes }) textForTrue!: string;
  @Prop({ default: T.wordsNo }) textForFalse!: string;

  get currentValue(): any {
    return this.selectedValue ?? this.valueForFalse;
  }

  onChange(newValue: any): void {
    this.$emit('update:value', newValue);
  }
}
</script>

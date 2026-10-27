<template>
  <input
    ref="input"
    class="typeahead form-control"
    autocomplete="off"
    :placeholder="placeholder"
    :name="name"
    :value="value"
    @change="onUpdateInput"
  />
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';

export default defineComponent({
  name: 'Autocomplete',
  props: {
    value: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: '',
    },
    name: {
      type: String,
      default: '',
    },
    init: {
      // eslint-disable-next-line no-undef -- This is defined in TypeScript.
      type: Function as PropType<(el: JQuery<HTMLElement>) => void>,
      required: true,
    },
  },
  watch: {
    value(newValue: string) {
      (this.$refs.input as HTMLInputElement).value = newValue;
    },
  },
  mounted() {
    this.init($(this.$refs.input as HTMLElement));
  },
  methods: {
    onUpdateInput(): string {
      const input = this.$refs.input as HTMLInputElement;
      const value = input.getAttribute('data-value');
      if (value !== null) {
        this.$emit('update:value', value);
      }
      this.$emit('input', input.value);
      return input.value;
    },
  },
});
</script>

<style lang="scss">
.tt-dataset {
  background: white;
  padding: 10px;
  border: 1px solid gray;
}
</style>

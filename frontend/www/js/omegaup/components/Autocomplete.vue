<template>
  <input
    ref="input"
    class="typeahead form-control"
    autocomplete="off"
    v-bind:placeholder="placeholder"
    v-bind:name="name"
    v-bind:value="value"
    v-on:change="onUpdateInput"
  />
</template>

<style lang="scss">
.tt-dataset {
  background: white;
  padding: 10px;
  border: 1px solid gray;
}
</style>

<script lang="ts">
import { Vue, Component, Watch, Prop, Emit, Ref } from 'vue-property-decorator';

@Component
export default class Autocomplete extends Vue {
  @Ref() input!: HTMLInputElement;
  @Prop() value!: string;
  @Prop() placeholder!: string;
  @Prop() name!: string;
  @Prop() init!: (el: JQuery<HTMLElement>) => void;

  mounted() {
    this.init($(<HTMLElement>this.$refs.input));
  }

  @Emit('input')
  onUpdateInput(): string {
    const value = this.input.getAttribute('data-value');
    if (value !== null) {
      this.$emit('update:value', value);
    }
    return this.input.value;
  }

  @Watch('value')
  onPropertyChanged(newValue: string) {
    this.input.value = newValue;
  }
}
</script>

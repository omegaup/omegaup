<template>
  <input
    class="typeahead form-control"
    ref="input"
    autocomplete="off"
    v-on:change="onUpdateInput"
    v-bind:placeholder="placeholder"
    v-bind:name="name"
    v-bind:value="value"
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
    this.init($(<HTMLElement>this.$el));
  }

  @Emit('input')
  onUpdateInput(): string {
    return this.input.value;
  }

  @Watch('value')
  onPropertyChanged(newValue: string, oldValue: string) {
    this.input.value = newValue;
  }
}
</script>

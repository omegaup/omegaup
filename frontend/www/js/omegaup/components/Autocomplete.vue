<template>
  <div>
    <input
      class="typeahead form-control"
      ref="input"
      autocomplete="off"
      v-on:change="onUpdateInput"
      v-bind:placeholder="placeholder"
      v-bind:name="name"
      v-bind:value="value"
    />
    <input type="hidden" ref="inputAlias" v-bind:value="alias" />
  </div>
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
  @Ref() inputAlias!: HTMLInputElement;
  @Prop() value!: string;
  @Prop() placeholder!: string;
  @Prop() name!: string;
  @Prop() init!: (el: JQuery<HTMLElement>) => void;

  alias = '';

  mounted() {
    this.init($(<HTMLElement>this.$el).find('.typeahead'));
  }

  @Emit('input')
  onUpdateInput(): string {
    return this.input.value;
  }

  @Watch('value')
  onValueChanged(newValue: string, oldValue: string) {
    const alias = this.input.getAttribute('data-alias');
    if (alias !== null) {
      this.alias = alias;
    }
    this.input.value = newValue;
  }

  @Watch('alias')
  onAliasChanged(newValue: string, oldValue: string) {
    this.$emit('emit-update-input', newValue);
  }
}
</script>

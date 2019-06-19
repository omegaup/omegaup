<template>
  <input class="typeahead form-control"
        ref="input"
        v-on:change="onUpdateInput">
</template>

<script lang="ts">
import { Vue, Component, Watch, Prop, Emit } from 'vue-property-decorator';

@Component
export default class Autocomplete extends Vue {
  $refs!: {
    input: HTMLInputElement;
  };

  @Prop() value!: string;
  @Prop() init!: Function;

  mounted() {
    this.init($(this.$el));
  }

  @Emit('input')
  onUpdateInput(): string {
    return this.$refs.input.value;
  }

  @Watch('value')
  onPropertyChanged(newValue: string, oldValue: string) {
    (<HTMLInputElement>this.$el).value = newValue;
  }
}

</script>

<template>
  <div class="root d-flex flex-row h-100">
    <textarea
      v-model="contents"
      class="col pl-1"
      :class="theme"
      :disabled="readOnly"
      :data-title="title"
    ></textarea>
  </div>
</template>

<script lang="ts">
import { Vue, Prop, Component } from 'vue-property-decorator';
import store from './GraderStore';

@Component
export default class TextEditor extends Vue {
  // TODO: place more restrictions on value of keys inside storeMapping
  @Prop({ required: true }) storeMapping!: {
    [key: string]: string;
  };
  @Prop({ required: true }) extension!: string;
  @Prop({ default: 'NA' }) module!: string;
  @Prop({ default: false }) readOnly!: boolean;

  get theme(): string {
    return store.getters['theme'];
  }

  get filename(): string {
    if (this.storeMapping.module) {
      return `${store.getters[this.storeMapping.module]}.${this.extension}`;
    }
    return `${this.module}.${this.extension}`;
  }

  get contents(): string {
    return store.getters[this.storeMapping.contents];
  }

  set contents(value: string) {
    if (this.readOnly) return;
    store.dispatch(this.storeMapping.contents, value);
  }

  get title(): string {
    return this.filename;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';

textarea {
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
  border: 0px;
  resize: none;

  &.vs {
    background: var(--vs-background-color);
    color: var(--vs-font-color);
  }

  &.vs-dark {
    background: var(--vs-dark-background-color);
    color: var(--vs-dark-font-color);
  }
}
</style>

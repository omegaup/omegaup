<template>
  <div class="root d-flex flex-row h-100">
    <textarea
      v-model="contents"
      class="col px-0"
      :class="theme"
      :disabled="readOnly"
    ></textarea>
  </div>
</template>

<script lang="ts">
// TODO: replace all instances of any with correct type
import { Vue, Prop, Component } from 'vue-property-decorator';
import * as Util from './util';

@Component
export default class TextEditor extends Vue {
  @Prop({ required: true }) store!: any;
  @Prop({ required: true }) storeMapping!: any;
  @Prop({ required: true }) extension!: string;
  @Prop({ default: null }) module!: string | null;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ default: 'vs' }) theme!: string;

  get filename(): string {
    if (typeof this.storeMapping.module !== 'undefined') {
      return `${Util.vuexGet(this.store, this.storeMapping.module)}.${
        this.extension
      }`;
    }
    return `${this.module}.${this.extension}`;
  }

  get contents(): string {
    return Util.vuexGet(this.store, this.storeMapping.contents);
  }

  set contents(value: string) {
    if (this.readOnly) return;
    Util.vuexSet(this.store, this.storeMapping.contents, value);
  }

  get title(): string {
    return this.filename;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../sass/main.scss';
.textarea.vs-dark {
  background: var(--textarea-vs-dark-background-color);
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
  color: var(--textarea-vs-dark-font-color);
}

.textarea.vs {
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
}
</style>

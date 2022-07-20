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
import { Vue, Component, Prop } from 'vue-property-decorator';
import { Store } from 'vuex';
import { State, StoreMapping } from './CaseSelectorTypescript.vue';
import * as Util from './util';

@Component
export default class GraderTextEditor extends Vue {
  @Prop({ required: true }) store!: Store<State>;
  @Prop({ required: true }) storeMapping!: StoreMapping;
  @Prop({ default: 'vs-dark' }) theme!: string;
  @Prop({ default: null }) module!: null | string;
  @Prop({ default: false }) readOnly!: boolean;
  @Prop({ required: true }) extension!: string;
  @Prop({ default: null }) initialLanguage!: null | string;

  get filename(): string {
    if (typeof this.storeMapping.module !== 'undefined') {
      const module = Util.vuexGet(this.store, this.storeMapping.module);
      return `${module}.${this.extension}`;
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

  title(): string {
    return this.filename;
  }
}
</script>

<style>
textarea.vs-dark {
  background: #222;
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
  color: #d4d4d4;
}

textarea.vs {
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
}
</style>

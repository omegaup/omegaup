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
import { Vue, Prop, Component } from 'vue-property-decorator';
import * as Util from '../grader/util';

@Component
export default class MonacoEditorComponent extends Vue {
  @Prop({ type: Object, required: true }) store!: any;
  @Prop({ type: Object, required: true }) storeMapping!: any;
  @Prop({ type: String, required: true }) extension!: string;
  @Prop({ type: String, default: null }) module!: string | null;
  @Prop({ type: Boolean, default: false }) readOnly!: boolean;
  @Prop({ type: String, default: 'vs' }) theme!: string;

  get filename(): string {
    if (typeof this.storeMapping.module !== 'undefined') {
      return (
        Util.vuexGet(this.store, this.storeMapping.module) +
        '.' +
        this.extension
      );
    }
    return this.module + '.' + this.extension;
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

<style scoped>
.textarea.vs-dark {
  background: #222;
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
  color: #d4d4d4;
}

.textarea.vs {
  border: 0px;
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
}
</style>

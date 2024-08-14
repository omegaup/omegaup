<template>
  <div
    class="root d-flex flex-row h-100"
    :class="{
      'bg-dark': theme === 'vs-dark',
      'text-white': theme === 'vs-dark',
    }"
  >
    <div class="filenames">
      <div class="list-group">
        <button
          v-if="!zip"
          class="text-truncate list-group-item list-group-item-action disabled"
          type="button"
        >
          <em>{{ T.wordsEmpty }}</em>
        </button>
        <button
          v-for="(item, name) in zip.files"
          v-else
          :key="name"
          class="text-truncate list-group-item list-group-item-action"
          type="button"
          :class="{ active: active === name }"
          :title="name"
          @click="select(item)"
        >
          {{ name }}
        </button>
      </div>
    </div>
    <textarea v-model="contents" class="editor" readonly></textarea>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as Util from './util';
import T from '../lang';
import JSZip, { JSZipObject } from 'jszip';
import store from './GraderStore';

@Component
export default class ZipViewer extends Vue {
  @Prop({ default: 'vs' }) theme!: string;

  zip: JSZip | null = null;
  active: string | null = null;
  T = T;

  get contents(): string {
    return store.getters.zipContent;
  }

  select(item: JSZipObject): void {
    item
      .async('string')
      .then((value: string) => {
        store.dispatch('zipContent', value);
      })
      .catch(Util.asyncError);
    this.active = item.name;
  }
}
</script>

<style scoped>
div.filenames {
  overflow-y: auto;
}

button.list-group-item {
  width: 10em;
}

textarea {
  flex: 1;
}
</style>

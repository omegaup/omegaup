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
          class="list-group-item list-group-item-action disabled"
          type="button"
        >
          <em>{{ T.wordsEmpty }}</em>
        </button>
        <button
          v-for="(item, name) in zip.files"
          v-else
          :key="name"
          class="list-group-item list-group-item-action"
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

@Component
export default class ZipViewer extends Vue {
  @Prop({ default: 'vs' }) theme!: string;

  zip: JSZip | null = null;
  active: string | null = null;
  contents: string = '';
  T = T;

  select(item: JSZipObject): void {
    this.active = item.name;
    item
      .async('string')
      .then((value: string) => {
        this.contents = value;
      })
      .catch(Util.asyncError);
  }
}
</script>

<style scoped>
div.filenames {
  overflow-y: auto;
}

button.list-group-item {
  width: 10em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

textarea {
  flex: 1;
}
</style>

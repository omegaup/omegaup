<template>
  <div
    class="root d-flex flex-row h-100 bg-dark text-white"
    :class="{ 'bg-dark': theme == 'vs-dark', 'text-white': theme == 'vs-dark' }"
  >
    <div class="filenames">
      <div class="list-group">
        <button
          v-if="!zip"
          class="list-group-item list-group-item-action disabled"
          type="button"
        >
          <em>Empty</em>
        </button>
        <button
          v-for="(item, name) in zip.files"
          v-else
          :key="name"
          class="list-group-item list-group-item-action"
          type="button"
          :class="{ active: active == name }"
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

@Component
export default class GraderZipViewer extends Vue {
  @Prop({ default: 'vs-dark' }) theme!: string;

  zip: { files: string[] } | null = null;
  active: null | string = null;
  contents: null | string = null;

  select(item: any): void {
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

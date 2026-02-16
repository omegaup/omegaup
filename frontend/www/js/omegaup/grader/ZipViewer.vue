<template>
  <div class="root d-flex flex-row h-100" :class="theme">
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
          :class="{
            active: active === name,
          }"
          :title="name"
          @click="select(item)"
        >
          {{ name }}
        </button>
      </div>
    </div>
    <textarea
      v-model="contents"
      class="editor"
      :class="theme"
      readonly
    ></textarea>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import * as Util from './util';
import T from '../lang';
import JSZip, { JSZipObject } from 'jszip';
import store from './GraderStore';

@Component
export default class ZipViewer extends Vue {
  zip: JSZip | null = null;
  active: string | null = null;
  T = T;

  get theme(): string {
    return store.getters['theme'];
  }

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
  font-family: 'Droid Sans Mono', 'Courier New', monospace,
    'Droid Sans Fallback';
  border: 0px;
  resize: none;
}

.vs {
  background: var(--vs-background-color);
  color: var(--vs-font-color);
}
.vs-dark {
  background: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
}
.vs-dark .filenames {
  background-color: var(--vs-dark-background-color);
}

.vs-dark .list-group-item {
  background-color: var(--vs-dark-background-color);
  color: var(--vs-dark-font-color);
  border-color: var(--vs-dark-border-color-medium);
}

.vs-dark .list-group-item-action {
  color: var(--vs-dark-font-color);
}

.vs-dark .list-group-item-action:hover,
.vs-dark .list-group-item-action:focus {
  background-color: var(
    --vs-dark-list-group-item-action-background-color--hover
  );
  color: var(--vs-dark-font-color);
}

.vs-dark .list-group-item-action.active {
  background-color: var(
    --vs-dark-list-group-item-action-background-color--active
  );
  border-color: var(--vs-dark-border-color-strong);
  color: var(--vs-dark-font-color);
}

.vs-dark .list-group-item.disabled {
  background-color: var(--vs-dark-list-group-item-disabled-background-color);
  color: var(--vs-dark-list-group-item-disabled-color);
}
</style>

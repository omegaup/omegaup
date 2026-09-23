<template>
  <div class="zip-viewer" :class="theme">
    <div class="files-sidebar">
      <div class="sidebar-header">
        <i class="fas fa-folder-open" aria-hidden="true"></i>
        <span>Output Files</span>
      </div>
      <div class="files-list">
        <div v-if="!zip" class="empty-state">
          <i class="fas fa-archive fa-3x empty-icon" aria-hidden="true"></i>
          <p>No output files</p>
        </div>
        <button
          v-for="(item, name) in zip ? zip.files : {}"
          v-else
          :key="name"
          class="file-item"
          :class="{ 'file-item--active': active === name }"
          type="button"
          :title="name"
          @click="select(item)"
        >
          <i class="far fa-file-alt file-icon" aria-hidden="true"></i>
          <span class="file-item-name">{{ name }}</span>
        </button>
      </div>
    </div>
    <div class="content-area">
      <div v-if="!contents" class="content-empty">
        <i class="fas fa-mouse-pointer fa-2x empty-icon" aria-hidden="true"></i>
        <p>Select a file to view its contents</p>
      </div>
      <pre v-else class="content-view"><code>{{ contents }}</code></pre>
    </div>
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

<style lang="scss" scoped>
.zip-viewer {
  display: flex;
  height: 100%;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  border-top: 1px solid #e5e7eb;

  .vs-dark & {
    border-top-color: #333;
  }
}

.files-sidebar {
  width: 250px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  border-right: 1px solid #e5e7eb;
  background: #f9fafb;

  .vs-dark & {
    background: #252525;
    border-right-color: #333;
  }
}

.sidebar-header {
  padding: 12px 16px;
  font-size: 11px;
  font-weight: 700;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  display: flex;
  align-items: center;
  gap: 8px;
  border-bottom: 1px solid #e5e7eb;

  i {
    font-size: 14px;
  }

  .vs-dark & {
    color: #9ca3af;
    border-bottom-color: #333;
  }
}

.files-list {
  flex: 1;
  overflow-y: auto;
  padding: 8px 0;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px 16px;
  color: #9ca3af;
  text-align: center;

  .empty-icon {
    margin-bottom: 12px;
    opacity: 0.5;
  }

  p {
    margin: 0;
    font-size: 13px;
  }

  .vs-dark & {
    color: #6b7280;
  }
}

.file-item {
  display: flex;
  align-items: center;
  width: 100%;
  padding: 8px 16px;
  gap: 8px;
  border: none;
  background: transparent;
  color: #4b5563;
  font-size: 13px;
  text-align: left;
  cursor: pointer;
  transition: background 0.15s;
  border-left: 3px solid transparent;

  .file-icon {
    color: #9ca3af;
  }

  &:hover {
    background: #e5e7eb;
  }

  &.file-item--active {
    background: #eff6ff;
    color: #1d4ed8;
    font-weight: 500;
    border-left-color: #3b82f6;

    .file-icon {
      color: #3b82f6;
    }
  }

  .vs-dark & {
    color: #d1d5db;

    .file-icon {
      color: #6b7280;
    }

    &:hover {
      background: #333;
    }

    &.file-item--active {
      background: rgba(59, 130, 246, 0.15);
      color: #60a5fa;

      .file-icon {
        color: #60a5fa;
      }
    }
  }
}

.file-item-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.content-area {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  background: #fff;

  .vs-dark & {
    background: #1e1e1e;
  }
}

.content-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #9ca3af;
  padding: 48px 24px;
  text-align: center;

  .empty-icon {
    margin-bottom: 16px;
    opacity: 0.3;
  }

  p {
    margin: 0;
    font-size: 13px;
  }

  .vs-dark & {
    color: #6b7280;
  }
}

.content-view {
  flex: 1;
  overflow: auto;
  margin: 0;
  padding: 16px;
  font-family: 'JetBrains Mono', 'Fira Code', 'Monaco', 'Menlo', 'Courier New',
    monospace;
  font-size: 12px;
  line-height: 1.6;
  background: #fff;
  color: #1a1a1a;
  white-space: pre;
  tab-size: 4;

  .vs-dark & {
    background: #1e1e1e;
    color: #d4d4d4;
  }
}
</style>

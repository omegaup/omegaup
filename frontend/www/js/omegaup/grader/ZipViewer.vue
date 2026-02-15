<template>
  <div class="zip-viewer" :class="theme">
    <div class="files-sidebar">
      <div class="sidebar-header">
        <svg width="14" height="14" viewBox="0 0 14 14" fill="currentColor">
          <path d="M12 0H6L4 2H0v10a2 2 0 002 2h10a2 2 0 002-2V2a2 2 0 00-2-2z"/>
        </svg>
        <span>Output Files</span>
      </div>
      <div class="files-list">
        <div v-if="!zip" class="empty-state">
          <svg width="40" height="40" viewBox="0 0 40 40" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="8" y="6" width="24" height="28" rx="2"/>
            <line x1="14" y1="14" x2="26" y2="14"/>
            <line x1="14" y1="20" x2="26" y2="20"/>
            <line x1="14" y1="26" x2="20" y2="26"/>
          </svg>
          <p>No output files</p>
        </div>
        <button
          v-for="(item, name) in (zip ? zip.files : {})"
          v-else
          :key="name"
          class="file-item"
          :class="{ 'file-item--active': active === name }"
          type="button"
          :title="name"
          @click="select(item)"
        >
          <svg width="12" height="12" viewBox="0 0 12 12" fill="currentColor" class="file-item-icon">
            <path d="M7 0H2a1 1 0 00-1 1v10a1 1 0 001 1h8a1 1 0 001-1V3L7 0zm3 10H2V2h4v2h4v6z"/>
          </svg>
          <span class="file-item-name">{{ name }}</span>
        </button>
      </div>
    </div>
    <div class="content-area">
      <div v-if="!contents" class="content-empty">
        <svg width="48" height="48" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 8h20v4H14zM14 20h20v4H14zM14 32h12v4H14z"/>
          <rect x="8" y="4" width="32" height="40" rx="2"/>
        </svg>
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
  background: #fff;

  &.vs-dark {
    background: #1e1e1e;
  }
}

/* Sidebar */
.files-sidebar {
  width: 240px;
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
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  border-bottom: 1px solid #e5e7eb;
  font-size: 13px;
  font-weight: 600;
  color: #1a1a1a;

  svg {
    color: #6b7280;
  }

  .vs-dark & {
    border-bottom-color: #333;
    color: #e5e5e5;

    svg {
      color: #9ca3af;
    }
  }
}

.files-list {
  flex: 1;
  overflow-y: auto;
  padding: 4px;
}

.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 16px;
  color: #9ca3af;
  text-align: center;

  svg {
    margin-bottom: 12px;
    opacity: 0.5;
  }

  p {
    margin: 0;
    font-size: 12px;
  }

  .vs-dark & {
    color: #6b7280;
  }
}

.file-item {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  padding: 8px 12px;
  border: none;
  border-radius: 6px;
  background: transparent;
  color: #4b5563;
  font-size: 12px;
  text-align: left;
  cursor: pointer;
  transition: all 0.12s;
  margin-bottom: 2px;

  &:hover {
    background: #fff;

    .vs-dark & {
      background: rgba(255, 255, 255, 0.05);
    }
  }

  &.file-item--active {
    background: #eff6ff;
    color: #1a73e8;
    font-weight: 500;

    .file-item-icon {
      color: #3b82f6;
    }

    .vs-dark & {
      background: rgba(59, 130, 246, 0.15);
      color: #60a5fa;
    }
  }

  .vs-dark & {
    color: #9ca3af;
  }
}

.file-item-icon {
  flex-shrink: 0;
  color: #9ca3af;

  .vs-dark & {
    color: #6b7280;
  }
}

.file-item-name {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Content area */
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

  svg {
    margin-bottom: 16px;
    opacity: 0.4;
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
  font-family: 'JetBrains Mono', 'Fira Code', 'Monaco', 'Menlo', 'Courier New', monospace;
  font-size: 12px;
  line-height: 1.6;
  background: #fff;
  color: #1a1a1a;
  white-space: pre;
  tab-size: 4;

  code {
    font-family: inherit;
    font-size: inherit;
  }

  .vs-dark & {
    background: #1e1e1e;
    color: #d4d4d4;
  }
}

/* Scrollbar styling */
.files-list::-webkit-scrollbar,
.content-view::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.files-list::-webkit-scrollbar-track,
.content-view::-webkit-scrollbar-track {
  background: transparent;
}

.files-list::-webkit-scrollbar-thumb,
.content-view::-webkit-scrollbar-thumb {
  background: #d1d5db;
  border-radius: 4px;

  &:hover {
    background: #9ca3af;
  }

  .vs-dark & {
    background: #404040;

    &:hover {
      background: #525252;
    }
  }
}
</style>
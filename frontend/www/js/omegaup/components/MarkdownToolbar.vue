<template>
  <div
    class="markdown-toolbar"
    data-markdown-toolbar
    role="toolbar"
    aria-label="Markdown formatting"
  >
    <button
      v-for="button in visibleButtons"
      :key="button.id"
      type="button"
      class="markdown-toolbar-button"
      v-bind="buttonAttrs(button)"
      :title="button.title"
      :aria-label="button.title"
      @click.prevent="onButtonClick(button)"
    >
      <!-- eslint-disable-next-line vue/no-v-html -->
      <span class="markdown-toolbar-icon" v-html="button.icon"></span>
    </button>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';

type ToolbarButton = {
  id: string;
  title: string;
  icon: string;
  kind: 'wrap' | 'linePrefix' | 'hr';
  prefix?: string;
  suffix?: string;
  placeholder?: string;
};

const ICON = {
  bold:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 12a4 4 0 0 0 0-8H6v8"/><path d="M15 20a4 4 0 0 0 0-8H6v8Z"/></svg>',
  italic:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" x2="10" y1="4" y2="4"/><line x1="14" x2="5" y1="20" y2="20"/><line x1="15" x2="9" y1="4" y2="20"/></svg>',
  link:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
  quote:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/></svg>',
  code:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16,18 22,12 16,6"/><polyline points="8,6 2,12 8,18"/></svg>',
  image:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>',
  olist:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="10" x2="21" y1="6" y2="6"/><line x1="10" x2="21" y1="12" y2="12"/><line x1="10" x2="21" y1="18" y2="18"/><path d="M4 6h1.5"/><path d="M4 12h1.5"/><path d="M4 18h1.5"/></svg>',
  ulist:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" x2="21" y1="6" y2="6"/><line x1="8" x2="21" y1="12" y2="12"/><line x1="8" x2="21" y1="18" y2="18"/><line x1="3" x2="3.01" y1="6" y2="6"/><line x1="3" x2="3.01" y1="12" y2="12"/><line x1="3" x2="3.01" y1="18" y2="18"/></svg>',
  heading:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 12h12"/><path d="M6 20V4"/><path d="M18 20V4"/></svg>',
  hr:
    '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/></svg>',
};

@Component
export default class MarkdownToolbar extends Vue {
  @Prop({ default: null }) getTextarea!:
    | (() => HTMLTextAreaElement | null)
    | null;
  @Prop({ default: false }) hideImage!: boolean;

  buttons: ToolbarButton[] = [
    {
      id: 'bold',
      title: 'Bold (Ctrl+B)',
      icon: ICON.bold,
      kind: 'wrap',
      prefix: '**',
      suffix: '**',
      placeholder: 'strong text',
    },
    {
      id: 'italic',
      title: 'Italic (Ctrl+I)',
      icon: ICON.italic,
      kind: 'wrap',
      prefix: '*',
      suffix: '*',
      placeholder: 'emphasized text',
    },
    {
      id: 'link',
      title: 'Hyperlink (Ctrl+L)',
      icon: ICON.link,
      kind: 'wrap',
      prefix: '[',
      suffix: '](url)',
      placeholder: 'enter link description here',
    },
    {
      id: 'quote',
      title: 'Blockquote (Ctrl+Q)',
      icon: ICON.quote,
      kind: 'linePrefix',
      prefix: '> ',
    },
    {
      id: 'code',
      title: 'Code Sample (Ctrl+K)',
      icon: ICON.code,
      kind: 'wrap',
      prefix: '`',
      suffix: '`',
      placeholder: 'enter code here',
    },
    {
      id: 'image',
      title: 'Image (Ctrl+G)',
      icon: ICON.image,
      kind: 'wrap',
      prefix: '![',
      suffix: '](url)',
      placeholder: 'enter image description here',
    },
    {
      id: 'olist',
      title: 'Numbered List (Ctrl+O)',
      icon: ICON.olist,
      kind: 'linePrefix',
      prefix: '1. ',
    },
    {
      id: 'ulist',
      title: 'Bulleted List (Ctrl+U)',
      icon: ICON.ulist,
      kind: 'linePrefix',
      prefix: '- ',
    },
    {
      id: 'heading',
      title: 'Heading (Ctrl+H)',
      icon: ICON.heading,
      kind: 'linePrefix',
      prefix: '## ',
    },
    {
      id: 'hr',
      title: 'Horizontal Rule (Ctrl+R)',
      icon: ICON.hr,
      kind: 'hr',
    },
  ];

  get visibleButtons(): ToolbarButton[] {
    if (!this.hideImage) {
      return this.buttons;
    }
    return this.buttons.filter((button) => button.id !== 'image');
  }

  buttonAttrs(button: ToolbarButton): { [key: string]: boolean } {
    return { [`data-markdown-toolbar-${button.id}`]: true };
  }

  onButtonClick(button: ToolbarButton): void {
    if (button.kind === 'wrap') {
      this.wrapSelection(
        button.prefix || '',
        button.suffix || '',
        button.placeholder || '',
      );
      return;
    }
    if (button.kind === 'linePrefix') {
      this.prefixLines(button.prefix || '');
      return;
    }
    this.insertHorizontalRule();
  }

  private inputElement(): HTMLTextAreaElement | null {
    return this.getTextarea ? this.getTextarea() : null;
  }

  private wrapSelection(
    prefix: string,
    suffix: string,
    placeholder: string,
  ): void {
    const el = this.inputElement();
    if (!el) {
      return;
    }
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const value = el.value;
    const selected = value.slice(start, end);
    const inner = selected || placeholder;
    const next = `${value.slice(
      0,
      start,
    )}${prefix}${inner}${suffix}${value.slice(end)}`;
    const selStart = start + prefix.length;
    this.commit(next, selStart, selStart + inner.length);
  }

  private prefixLines(prefix: string): void {
    const el = this.inputElement();
    if (!el) {
      return;
    }
    const start = el.selectionStart;
    const end = el.selectionEnd;
    const value = el.value;
    const lineStart = value.lastIndexOf('\n', Math.max(0, start - 1)) + 1;
    const lineEnd =
      end === start
        ? value.indexOf('\n', end)
        : value.indexOf('\n', Math.max(end - 1, lineStart));
    const blockEnd = lineEnd === -1 ? value.length : lineEnd;
    const block = value.slice(lineStart, blockEnd) || '';
    const lines = (block === '' ? [''] : block.split('\n')).map((line) =>
      line.startsWith(prefix) ? line : `${prefix}${line}`,
    );
    const replacement = lines.join('\n');
    const next = `${value.slice(0, lineStart)}${replacement}${value.slice(
      blockEnd,
    )}`;
    this.commit(next, lineStart, lineStart + replacement.length);
  }

  private insertHorizontalRule(): void {
    const el = this.inputElement();
    if (!el) {
      return;
    }
    const start = el.selectionStart;
    const value = el.value;
    const rule = `${
      start > 0 && value[start - 1] !== '\n' ? '\n' : ''
    }----------\n`;
    const next = `${value.slice(0, start)}${rule}${value.slice(
      el.selectionEnd,
    )}`;
    const caret = start + rule.length;
    this.commit(next, caret, caret);
  }

  private commit(
    next: string,
    selectionStart: number,
    selectionEnd: number,
  ): void {
    const el = this.inputElement();
    this.$emit('input', next);
    this.$nextTick(() => {
      if (!el) {
        return;
      }
      el.focus();
      el.setSelectionRange(selectionStart, selectionEnd);
    });
  }
}
</script>

<style lang="scss" scoped>
.markdown-toolbar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.25rem;
  min-height: 2rem;
  padding: 0.25rem 0;
  background-color: var(--wmd-button-bar-background-color, #fff);
}

.markdown-toolbar-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.75rem;
  height: 1.75rem;
  padding: 0;
  border: 0;
  border-radius: 0.25rem;
  background: transparent;
  color: inherit;
  cursor: pointer;

  &:hover,
  &:focus {
    background-color: rgba(0, 0, 0, 0.06);
    outline: none;
  }
}

.markdown-toolbar-icon {
  display: inline-flex;
  width: 20px;
  height: 20px;
}

/* stylelint-disable-next-line selector-pseudo-element-no-unknown */
.markdown-toolbar-icon ::v-deep svg {
  display: block;
}
</style>

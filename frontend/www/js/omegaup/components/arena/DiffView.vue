<template>
  <div ref="cm-editor"></div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import * as CodeMirror from 'codemirror';
import 'codemirror/lib/codemirror.css';
import 'codemirror/addon/merge/merge.css';

require('../../../../third_party/js/diff_match_patch.js');
require('codemirror/addon/merge/merge.js');

// Jest does not quite handle imports the same way that webpack does (since
// webpack bundles everything directly, and Jest uses Node's `require`), so the
// place where the `MergeView` is located changes between regular compilation
// and tests.
const MergeView =
  CodeMirror.MergeView ?? (CodeMirror as any)?.default.MergeView;

export default defineComponent({
  name: 'DiffView',
  props: {
    left: {
      type: String,
      required: true,
    },
    right: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      editor: null as CodeMirror.MergeView.MergeViewEditor | null,
    };
  },
  mounted() {
    if (this.editor) return;
    this.editor = MergeView(this.$refs['cm-editor'] as HTMLElement, {
      collapseIdentical: true,
      connect: 'align',
      lineNumbers: true,
      mode: 'text/plain',
      orig: this.right,
      origRight: this.left,
      readOnly: true,
      revertButtons: false,
      showDifferences: true,
      tabSize: 2,
      value: this.right,
    });
  },
});
</script>

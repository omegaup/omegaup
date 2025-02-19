<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ filename }}</h2>
    </div>
    <div class="card">
      <div class="card-body">
        <omegaup-markdown :markdown="content"></omegaup-markdown>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CommonMarkdownViewer extends Vue {
  @Prop() content!: string;
  @Prop() filename!: string;

  T = T;
  ui = ui;
}
</script>

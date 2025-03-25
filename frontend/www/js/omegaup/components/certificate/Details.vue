<template>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        {{ T.certificateDetailsTitle }}
      </h4>
    </div>
    <div class="panel-body">
      <omegaup-markdown
        :markdown="
          ui.formatString(T.certificateDetailsBody, {
            uuid: encodeURIComponent(uuid),
          })
        "
      ></omegaup-markdown>
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
export default class CertificateDetails extends Vue {
  @Prop() uuid!: string;

  T = T;
  ui = ui;
}
</script>

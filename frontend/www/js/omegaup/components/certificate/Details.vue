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
import { defineComponent } from 'vue';
import T from '../../lang';
import * as ui from '../../ui';

import omegaup_Markdown from '../Markdown.vue';

export default defineComponent({
  name: 'CertificateDetails',
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
  props: {
    uuid: {
      type: String,
      required: true,
    },
  },
  data() {
    return {
      T,
      ui,
    };
  },
});
</script>

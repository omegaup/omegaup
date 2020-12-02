<template>
  <ul>
    <hr class="dropdown-separator" />
    <li class="grader grader-submissions">
      <a class="grader-submissions-link" href="/arena/admin/">{{
        T.wordsLatestSubmissions
      }}</a>
    </li>
    <li class="grader grader-status">{{ graderStatusMessage }}</li>
    <li v-if="status === 'ok'" class="grader grader-broadcaster-sockets">
      Broadcaster sockets:
      {{ graderInfo !== null ? graderInfo.broadcaster_sockets : '' }}
    </li>
    <li v-else-if="error !== null" class="grader grader-broadcaster-sockets">
      API api/grader/status call failed:
      <pre style="width: 40em">{{ error }}</pre>
    </li>
    <li v-if="status === 'ok'" class="grader grader-embedded-runner">
      Embedded runner:
      {{ graderInfo !== null ? graderInfo.embedded_runner : '' }}
    </li>
    <li v-if="status === 'ok'" class="grader grader-queues">
      Queues:
      <pre v-if="graderInfo !== null" style="width: 40em">
        <omegaup-markdown
          :markdown="ui.prettyPrintJSON(graderInfo.queue)"
        ></omegaup-markdown>
      </pre>
    </li>
  </ul>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class GraderStatus extends Vue {
  @Prop() status!: string;
  @Prop() error!: string;
  @Prop() graderInfo!: types.GraderStatus | null;

  T = T;
  ui = ui;

  get graderStatusMessage(): string {
    return this.status === 'ok' ? 'Grader OK' : 'Grader DOWN';
  }
}
</script>

<style>
.grader-submissions,
a.grader-submissions-link {
  background-color: #fff;
  color: #000 !important;
  text-decoration: none;
}

.grader-submissions:hover,
a.grader-submissions-link:hover {
  background-color: #fff !important;
}

.grader {
  padding: 3px 20px;
}

hr.dropdown-separator {
  margin: 0;
}

li,
ul {
  padding: 0;
}

ol,
ul {
  list-style: none;
}

pre {
  padding: 9.5px;
  margin: 0 0 10px;
  line-height: 1.42857143;
  word-break: break-all;
  word-wrap: break-word;
  background-color: #f5f5f5;
  border: 1px solid #ccc;
  border-radius: 4px;
}
</style>

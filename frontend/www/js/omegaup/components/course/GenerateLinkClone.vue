<template>
  <div class="omegaup-course-clone card">
    <div class="card-body">
      <form class="form" v-on:submit.prevent="$emit('generate-link', alias)">
        <h4>{{ T.courseCloneGenerateLinkTitle }}</h4>
        <p>{{ T.courseCloneGenerateLinkDescription }}</p>
        <div class="row">
          <div class="input-group mx-3">
            <button class="btn btn-primary" type="submit">
              {{ T.courseCloneGenerateLinkButton }}
            </button>
            <input
              class="form-control input-group-append"
              v-bind:value="cloneCourseURL"
              readonly
              v-on:focus="$event.target.select()"
            />
            <div class="input-group-append">
              <button
                class="btn btn-outline-secondary"
                type="button"
                v-on:click="copiedToClipboard = true"
                v-clipboard="() => cloneCourseURL"
                v-bind:disabled="!cloneCourseURL"
                v-bind:title="T.wordsCopyToClipboard"
                data-copy-to-clipboard
              >
                <font-awesome-icon icon="clipboard" />
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import Clipboard from 'v-clipboard';
import T from '../../lang';
import * as ui from '../../ui';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);
Vue.use(Clipboard);

@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseGenerateLinkClone extends Vue {
  @Prop() alias!: string;
  @Prop() token!: string;

  T = T;
  copiedToClipboard = false;

  get cloneCourseURL(): string {
    if (!this.token) {
      return '';
    }
    return `${window.location.origin}/course/${this.alias}/clone/${this.token}/`;
  }

  @Watch('copiedToClipboard')
  onPropertyChanged(newValue: boolean): void {
    if (!newValue) return;
    ui.success(T.passwordResetLinkCopiedToClipboard);
  }
}
</script>

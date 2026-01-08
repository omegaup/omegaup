<template>
  <div class="omegaup-course-clone card">
    <div class="card-body">
      <form class="form" @submit.prevent="$emit('generate-link', alias)">
        <h4>{{ T.courseCloneGenerateLinkTitle }}</h4>
        <p>{{ T.courseCloneGenerateLinkDescription }}</p>
        <div class="row">
          <div class="input-group mx-3">
            <button class="btn btn-primary" type="submit">
              {{ T.courseCloneGenerateLinkButton }}
            </button>
            <input
              class="form-control input-group-append"
              :value="cloneCourseURL"
              readonly
              @focus="$event.target.select()"
            />
            <div class="input-group-append">
              <button
                v-clipboard="() => cloneCourseURL"
                class="btn btn-outline-secondary"
                type="button"
                :disabled="!cloneCourseURL"
                :title="T.wordsCopyToClipboard"
                data-copy-to-clipboard
                @click="copiedToClipboard = true"
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
(library as any).add(fas);
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

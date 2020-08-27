<template>
  <div class="omegaup-course-clone card">
    <div class="card-body">
      <form class="form" v-on:submit.prevent="$emit('generate-link', alias)">
        <h4>{{ T.courseCloneGenerateLinkTitle }}</h4>
        <p>{{ T.courseCloneGenerateLinkDescription }}</p>
        <div class="row">
          <div class="form-group col-md-12">
            <textarea
              class="form-control"
              v-html="cloneCourseURL"
              rows="5"
              readonly
            ></textarea>
          </div>
          <div class="form-group col-md-12">
            <button class="btn btn-primary mr-3" type="submit">
              {{ T.courseCloneGenerateLinkButton }}
            </button>
            <button
              class="btn btn-primary"
              type="button"
              v-on:click="copiedToClipboard = true"
              v-clipboard="() => cloneCourseURL"
              v-bind:disabled="!cloneCourseURL"
              data-copy-to-clipboard
            >
              {{ T.wordsCopyToClipboard }}
            </button>
            <span class="ml-3" data-copied v-if="copiedToClipboard === true">
              <font-awesome-icon
                icon="check-circle"
                size="2x"
                v-bind:style="{ color: 'green' }"
              />
              {{ T.passwordResetLinkCopiedToClipboard }}
            </span>
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
    setTimeout(() => (this.copiedToClipboard = false), 5000);
  }
}
</script>

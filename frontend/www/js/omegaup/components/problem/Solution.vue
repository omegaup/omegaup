<template>
  <div v-if="isDisabled" class="system-in-maintainance m-5 text-center">
    <omegaup-markdown
      :markdown="T.problemSolutionSystemInMaintainance"
    ></omegaup-markdown>
    <font-awesome-icon :icon="['fas', 'cogs']" />
  </div>
  <div v-else class="card">
    <div class="row p-3">
      <div class="col-12 text-right">
        <a :href="SolutionViewFeatureGuideURL"
          ><font-awesome-icon :icon="['fas', 'question-circle']" />
          {{ T.officialSolutionsInfo }}</a
        >
      </div>
    </div>

    <omegaup-markdown
      v-if="showSolution"
      :markdown="solution.markdown"
      :source-mapping="solution.sources"
      :image-mapping="solution.images"
    ></omegaup-markdown>
    <div v-else class="interstitial">
      <omegaup-markdown :markdown="statusMessage"></omegaup-markdown>
      <omegaup-markdown
        v-show="showViewsLeft"
        :markdown="
          ui.formatString(T.solutionViewsLeft, {
            available: allowedSolutionsToSee,
            total: 5,
          }),
        "
      ></omegaup-markdown>
      <div class="text-center mt-5">
        <button
          v-if="status === 'unlocked'"
          class="btn btn-primary btn-md"
          @click="$emit('get-solution')"
        >
          {{ T.wordsSeeSolution }}
          <font-awesome-icon :icon="['fas', 'unlock']" />
        </button>
        <button
          v-else-if="status === 'locked' && allowedSolutionsToSee === null"
          class="btn btn-secondary btn-md"
          @click="$emit('get-allowed-solutions')"
        >
          {{ T.solutionAvailableViews }}
        </button>
        <button
          v-else-if="status === 'locked' && allowedSolutionsToSee > 0"
          class="btn btn-primary btn-md"
          @click="$emit('unlock-solution')"
        >
          {{ T.wordsUnlockSolution }}
          <font-awesome-icon :icon="['fas', 'lock']" />
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';
import { types } from '../../api_types';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { library } from '@fortawesome/fontawesome-svg-core';
import {
  faLock,
  faUnlock,
  faQuestionCircle,
  faCogs,
} from '@fortawesome/free-solid-svg-icons';
import { getBlogUrl } from '../../urlHelper';
library.add(faLock);
library.add(faUnlock);
library.add(faQuestionCircle);
library.add(faCogs);

import omegaup_problemMarkdown from './ProblemMarkdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_problemMarkdown,
    FontAwesomeIcon,
  },
})
class ProblemSolution extends Vue {
  @Prop() status!: string;
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop() allowedSolutionsToSee!: number;
  @Prop({ default: true }) isDisabled!: boolean;

  T = T;
  ui = ui;

  get SolutionViewFeatureGuideURL(): string {
    return getBlogUrl('SolutionViewFeatureGuideURL');
  }
  get showSolution(): boolean {
    return this.status === 'unlocked' && this.solution !== null;
  }

  get statusMessage(): string {
    switch (this.status) {
      case 'unlocked':
        return T.solutionConfirm;
      case 'locked':
        return T.solutionLocked;
      case 'not_found':
        return T.solutionNotFound;
      case 'not_logged_in':
        return T.solutionNotLoggedIn;
      default:
        return '';
    }
  }
  get showViewsLeft(): boolean {
    return (
      this.allowedSolutionsToSee !== null && this.status !== 'not_logged_in'
    );
  }
}

export default ProblemSolution;
</script>

<style scoped lang="scss">
.interstitial {
  padding: 2em;
}

.solution {
  padding: 2em 7em;
}

.system-in-maintainance {
  font-size: 160%;
  color: var(--general-in-maintainance-color);
}
</style>

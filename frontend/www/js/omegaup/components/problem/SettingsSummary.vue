<template>
  <div>
    <h3 :data-problem-title="problem.alias" class="text-center mb-4">
      {{ title }}
      <template v-if="showVisibilityIndicators">
        <img
          v-if="problem.quality_seal || problem.visibility === 3"
          src="/media/quality-badge.png"
          :title="T.wordsHighQualityProblem"
          class="mr-2"
        />
        <font-awesome-icon
          v-if="problem.visibility === 1 || problem.visibility === -1"
          :icon="['fas', 'exclamation-triangle']"
          :title="T.wordsWarningProblem"
          class="mr-2"
        ></font-awesome-icon>
        <font-awesome-icon
          v-if="problem.visibility === 0 || problem.visibility === -1"
          :icon="['fas', 'eye-slash']"
          :title="T.wordsPrivate"
          class="mr-2"
        ></font-awesome-icon>
        <font-awesome-icon
          v-if="problem.visibility <= -2"
          :icon="['fas', 'ban']"
          :title="T.wordsBannedProblem"
          class="mr-2"
          color="darkred"
        ></font-awesome-icon>
      </template>

      <a v-if="showEditLink" :href="`/problem/${problem.alias}/edit/`">
        <font-awesome-icon :icon="['fas', 'edit']" />
      </a>
      <button
        v-if="userLoggedIn"
        data-bookmark-button
        class="btn btn-link p-0 ml-2"
        :title="isBookmarked ? T.problemBookmarkRemove : T.problemBookmarkAdd"
        @click.prevent.stop="onToggleBookmark"
      >
        <font-awesome-icon
          :icon="['fas', 'bookmark']"
          class="bookmark-icon"
          :class="{
            'bookmark-active': isBookmarked,
            'bookmark-inactive': !isBookmarked,
          }"
        />
      </button>
    </h3>
    <table
      v-if="problem.accepts_submissions"
      class="table table-bordered mx-auto w-75 mb-0"
    >
      <tr>
        <th class="align-middle" scope="row">{{ T.wordsPoints }}</th>
        <td class="align-middle">{{ problem.points }}</td>
        <th class="align-middle" scope="row">{{ T.arenaCommonMemoryLimit }}</th>
        <td class="align-middle" data-memory-limit>{{ memoryLimit }}</td>
      </tr>
      <tr>
        <th class="align-middle" scope="row">{{ T.arenaCommonTimeLimit }}</th>
        <td class="align-middle">{{ timeLimit }}</td>
        <th class="align-middle" scope="row">
          {{ T.arenaCommonOverallWallTimeLimit }}
        </th>
        <td class="align-middle">{{ overallWallTimeLimit }}</td>
      </tr>
      <tr>
        <template v-if="!showVisibilityIndicators">
          <th class="align-middle" scope="row">{{ T.wordsInOut }}</th>
          <td class="align-middle">{{ T.wordsConsole }}</td>
        </template>
        <th class="align-middle" scope="row">
          {{ T.problemEditFormInputLimit }}
        </th>
        <td class="align-middle">{{ inputLimit }}</td>
      </tr>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

import { library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import {
  faEdit,
  faExclamationTriangle,
  faEyeSlash,
  faBan,
  faExternalLinkAlt,
  faBookmark,
} from '@fortawesome/free-solid-svg-icons';
library.add(
  faExclamationTriangle,
  faEdit,
  faEyeSlash,
  faBan,
  faExternalLinkAlt,
  faBookmark,
);

@Component({
  components: {
    FontAwesomeIcon,
  },
})
export default class ProblemSettingsSummary extends Vue {
  @Prop() problem!: types.ArenaProblemDetails;
  @Prop({ default: null }) problemsetTitle!: null | string;
  @Prop({ default: false }) showVisibilityIndicators!: boolean;
  @Prop({ default: false }) showEditLink!: boolean;
  @Prop({ default: false }) userLoggedIn!: boolean;
  @Prop({ default: false }) isBookmarked!: boolean;

  T = T;

  onToggleBookmark(): void {
    this.$emit('toggle-bookmark', this.problem.alias);
  }

  get title(): string {
    if (this.showVisibilityIndicators) {
      return ui.formatString(T.problemSettingsSummaryTitleWithProblemId, {
        problem_id: this.problem.problem_id,
        problem_title: this.problem.title,
      });
    }
    if (this.problem.letter && this.problemsetTitle) {
      return ui.formatString(
        T.problemSettingsSummaryTitleWithProblemsetTitleAndLetter,
        {
          problemset_title: this.problemsetTitle,
          letter: this.problem.letter,
          problem_title: this.problem.title,
        },
      );
    }
    if (this.problem.letter && !this.problemsetTitle) {
      return ui.formatString(T.problemSettingsSummaryTitleWithLetter, {
        letter: this.problem.letter,
        problem_title: this.problem.title,
      });
    }
    return this.problem.title;
  }

  get memoryLimit(): string {
    if (!this.problem.settings?.limits.MemoryLimit) {
      return '';
    }
    if (typeof this.problem.settings?.limits.MemoryLimit === 'string') {
      return this.problem.settings?.limits.MemoryLimit;
    }
    const memoryLimit = this.problem.settings?.limits.MemoryLimit as number;
    return `${memoryLimit / 1024 / 1024} MiB`;
  }

  get timeLimit(): string {
    if (!this.problem.settings?.limits.TimeLimit) {
      return '';
    }
    return `${this.problem.settings?.limits.TimeLimit}`;
  }

  get overallWallTimeLimit(): string {
    if (!this.problem.settings?.limits.OverallWallTimeLimit) {
      return '';
    }
    return `${this.problem.settings?.limits.OverallWallTimeLimit}`;
  }

  get inputLimit(): string {
    if (!this.problem.input_limit) {
      return '';
    }
    return `${this.problem.input_limit / 1024} KiB`;
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

table td {
  padding: 0.5rem;
}

.bookmark-icon {
  font-size: 1.5em;
}

.bookmark-active {
  color: $omegaup-blue;
}

.bookmark-inactive {
  color: $omegaup-grey--lighter;
  opacity: 0.5;
}
</style>

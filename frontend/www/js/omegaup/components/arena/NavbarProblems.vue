<template>
  <div class="problem-list mr-3 mr-lg-0">
    <b-breadcrumb v-if="inAssignment" data-breadcrumbs>
      <b-breadcrumb-item href="/course/">{{ T.navCourses }}</b-breadcrumb-item>
      <b-breadcrumb-item :href="urlAssignment">{{
        courseName
      }}</b-breadcrumb-item>
      <b-breadcrumb-item v-if="currentAssignment" active>
        {{ currentAssignment.name }}
      </b-breadcrumb-item>
    </b-breadcrumb>
    <div class="summary" :class="{ active: !activeProblem }">
      <a
        class="name"
        href="#problems"
        @click="$emit('disable-active-problem')"
        >{{ T.wordsSummary }}</a
      >
    </div>
    <div
      v-for="problem in problems"
      :key="problem.alias"
      :class="{ active: problem.alias === activeProblem }"
      data-navbar-problem
    >
      <div class="row">
        <div class="col-xs-5 problem-type w-50 pl-4">
          <span v-if="inAssignment">{{
            getProblemTypeTitle(problem.acceptsSubmissions)
          }}</span>
        </div>
        <div
          v-if="problem.acceptsSubmissions"
          class="col-xs-7 solved text-right w-50 pr-3"
        >
          <span class="mr-1">{{ getMaxScoreForProblem(problem) }}</span>
          <font-awesome-icon
            v-if="
              problem.myBestScore == problem.maxScore ||
              problem.bestScore == problem.maxScore
            "
            icon="check"
            :style="{ color: 'green' }"
          />
          <font-awesome-icon
            v-else-if="
              problem.hasMyRuns ||
              (problem.hasRuns && problem.hasMyRuns === null)
            "
            icon="times"
            :style="{ color: 'red' }"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12 pl-4">
          <a
            :data-problem="problem.alias"
            class="name"
            @click="onNavigateToProblem(problem)"
            >{{ problem.text }}</a
          >
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue';
import { omegaup } from '../../omegaup';
import { types } from '../../api_types';
import T from '../../lang';

import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

import { BBreadcrumb, BBreadcrumbItem } from 'bootstrap-vue';

export default defineComponent({
  name: 'ArenaNavbarProblems',
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'b-breadcrumb': BBreadcrumb,
    'b-breadcrumb-item': BBreadcrumbItem,
  },
  props: {
    problems: {
      type: Array as PropType<types.NavbarProblemsetProblem[]>,
      default: () => [],
    },
    activeProblem: {
      type: String as PropType<string | null>,
      default: null,
    },
    courseAlias: {
      type: String as PropType<string | null>,
      default: null,
    },
    courseName: {
      type: String as PropType<string | null>,
      default: null,
    },
    inAssignment: {
      type: Boolean,
      default: false,
    },
    digitsAfterDecimalPoint: {
      type: Number,
      default: 2,
    },
    currentAssignment: {
      type: Object as PropType<omegaup.Assignment | null>,
      default: null,
    },
  },
  emits: ['disable-active-problem', 'navigate-to-problem'],
  data() {
    return {
      T,
    };
  },
  computed: {
    urlAssignment(): string {
      return `/course/${this.courseAlias}/`;
    },
  },
  methods: {
    getProblemTypeTitle(acceptsSubmissions: boolean): string {
      return acceptsSubmissions ? T.wordsProblem : T.wordsLecture;
    },
    getMaxScoreForProblem(problem: types.NavbarProblemsetProblem): string {
      const bestScore = problem.myBestScore ?? problem.bestScore;
      return `(${bestScore.toFixed(
        this.digitsAfterDecimalPoint,
      )} / ${problem.maxScore.toFixed(this.digitsAfterDecimalPoint)})`;
    },
    onNavigateToProblem(problem: types.NavbarProblemsetProblem) {
      this.$emit('navigate-to-problem', problem);
    },
  },
});
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.problem-list > div {
  width: 19em;
  margin-bottom: 0.5em;
  padding-top: 0.2em;
  background: var(--arena-contest-navbar-problem-list-background-color);
  border: solid 1px var(--arena-contest-navbar-problem-list-border-color);
  border-width: 1px 0 1px 1px;
  position: relative;
}

.problem-list > div a {
  color: var(--arena-contest-navbar-problem-list-a-font-color);
  display: block;
  padding: 0.5em;
  width: 100%;
  cursor: pointer;
}

.problem-list > div.active {
  background: white;
}

.problem-list > div.summary {
  margin-bottom: 1em;
}

.problem-list > div .solved {
  text-align: right;
  right: 1em;
}

.problem-list .problem-type {
  font-size: 13px;
  color: var(--arena-contest-navbar-problem-type-font-color);
  font-weight: bold;
}
</style>

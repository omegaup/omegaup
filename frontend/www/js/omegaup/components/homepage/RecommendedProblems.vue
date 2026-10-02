<template>
  <div v-if="problems.length" class="card h-100">
    <h5 class="card-header">
      {{ T.homepageRecommendedProblemsTitle }}
    </h5>
    <div class="list-group list-group-flush">
      <a
        v-for="problem in problems"
        :key="problem.alias"
        class="list-group-item list-group-item-action"
        :href="`/arena/problem/${problem.alias}/`"
        :data-recommended-problem="problem.alias"
      >
        <div class="font-weight-bold">{{ problem.title }}</div>
        <small class="text-muted">{{ reasonFor(problem) }}</small>
      </a>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import type { PropType } from 'vue';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';

export default defineComponent({
  name: 'RecommendedProblems',
  props: {
    problems: {
      type: Array as PropType<types.RecommendedProblem[]>,
      required: true,
    },
  },
  data() {
    return { T };
  },
  methods: {
    reasonFor(problem: types.RecommendedProblem): string {
      return ui.formatString(T.homepageRecommendedProblemsReason, {
        problem: problem.solved_problem_title,
      });
    },
  },
});
</script>

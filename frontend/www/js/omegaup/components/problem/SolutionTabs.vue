<template>
  <div class="tab-pane fade p-4" :class="{ 'show active': selectedTab === 'solution' }">
    <ul class="nav nav-tabs mb-4">
      <li class="nav-item" role="tablist">
        <a 
          href="#official-solution" 
          class="nav-link" 
          :class="{ active: selectedSolutionTab === 'official-solution' }"
          data-toggle="tab" 
          role="tab" 
          aria-controls="official-solution"
          :aria-selected="selectedSolutionTab === 'official-solution'"
          @click="onSolutionTabSelected('official-solution')"
        >
        Official Solution
        </a>
      </li>
      <li class="nav-item" role="tablist">
        <a 
          href="#official-solutions" 
          class="nav-link" 
          :class="{ active: selectedSolutionTab === 'official-solutions' }"
          data-toggle="tab" 
          role="tab" 
          aria-controls="official-solutions"
          :aria-selected="selectedSolutionTab === 'official-solutions'"
          @click="onSolutionTabSelected('official-solutions')"
        >
          Suggested Solutions
        </a>
      </li>
      <li class="nav-item" role="tablist">
        <a 
          href="#post-solution" 
          class="nav-link" 
          :class="{ active: selectedSolutionTab === 'post-solution' }"
          data-toggle="tab" 
          role="tab" 
          aria-controls="post-solution"
          :aria-selected="selectedSolutionTab === 'post-solution'"
          @click="onSolutionTabSelected('post-solution')"
        >
        Post solution
        </a>
      </li>
    </ul>
    
    <div class="tab-content">
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': selectedSolutionTab === 'official-solution' }"
        id="official-solution"
      >
        <omegaup-problem-solution
          :status="solutionStatus"
          :allowed-solutions-to-see="allowedSolutionsToSee"
          :solution="solution"
          @get-solution="$emit('get-solution')"
          @get-allowed-solutions="$emit('get-allowed-solutions')"
          @unlock-solution="$emit('unlock-solution')"
        >
        </omegaup-problem-solution>
      </div>
      
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': selectedSolutionTab === 'official-solutions' }"
        id="official-solutions"
      >
        <omegaup-problem-official-solutions></omegaup-problem-official-solutions>
      </div>
      
      <div 
        class="tab-pane fade" 
        :class="{ 'show active': selectedSolutionTab === 'post-solution' }"
        id="post-solution"
      >
        <omegaup-problem-post-solution></omegaup-problem-post-solution>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import problem_solution from './Solution.vue';
import problem_official_solutions from './OfficialSolutions.vue';
import problem_post_solution from './PostSolution.vue';

@Component({
  components: {
    'omegaup-problem-solution': problem_solution,
    'omegaup-problem-official-solutions': problem_official_solutions,
    'omegaup-problem-post-solution': problem_post_solution,
  },
})
export default class ProblemSolutionTabs extends Vue {
  @Prop() solutionStatus!: string;
  @Prop({ default: 0 }) allowedSolutionsToSee!: number;
  @Prop({ default: null }) solution!: types.ProblemStatement | null;
  @Prop({ default: 'solution' }) selectedTab!: string;

  T = T;
  selectedSolutionTab = 'official-solution';

  onSolutionTabSelected(tabName: string): void {
    this.selectedSolutionTab = tabName;
  }
}
</script>

<style lang="scss" scoped>
</style>
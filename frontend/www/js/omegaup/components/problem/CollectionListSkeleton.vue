<template>
  <div class="container-fluid p-5 max-width mx-auto">
    <!-- Header -->
    <div class="row">
      <div class="col col-md-3 d-flex align-items-center">
        <omegaup-skeleton height="1rem" width="100px" />
      </div>
      <div class="col mb-4">
        <omegaup-skeleton height="2.5rem" width="300px" />
      </div>
    </div>
    
    <div class="row">
      <!-- Sidebar Skeleton -->
      <div class="col col-md-3">
        <!-- Filter Tags -->
        <div class="mb-4">
          <omegaup-skeleton height="24px" width="100px" class="mb-3" />
          <div v-for="i in 5" :key="`tag-${i}`" class="mb-2">
             <omegaup-skeleton height="30px" width="100%" />
          </div>
        </div>
        
        <!-- Filter Difficulty -->
        <div class="mb-4">
          <omegaup-skeleton height="24px" width="120px" class="mb-3" />
          <omegaup-skeleton height="40px" width="100%" />
        </div>
        
        <!-- Filter Quality -->
        <div class="mb-4">
           <omegaup-skeleton height="24px" width="100px" class="mb-3" />
           <omegaup-skeleton height="40px" width="100%" />
        </div>
      </div>
      
      <!-- Main Content Skeleton -->
      <div class="col p-0">
         <omegaup-problem-list-skeleton />
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Skeleton from '../common/Skeleton.vue';
import ProblemListSkeleton from './Skeleton.vue';

@Component({
  components: {
    'omegaup-skeleton': Skeleton,
    'omegaup-problem-list-skeleton': ProblemListSkeleton,
  },
})
export default class CollectionListSkeleton extends Vue {}
</script>

<style scoped>
.max-width {
  max-width: 75rem;
}
</style>

<template>
  <div class="card h-100">
    <div class="card-header d-flex justify-content-center align-items-center">
       <omegaup-skeleton height="24px" width="180px" class="mr-2" />
       <omegaup-skeleton circle height="16px" width="16px" />
    </div>
    <div class="card-body text-center d-flex flex-column justify-content-center align-items-center">
       <div class="d-flex align-items-center mb-2">
         <omegaup-skeleton circle height="16px" width="16px" class="mr-1" />
         <omegaup-skeleton height="1.25rem" width="200px" />
       </div>
       <omegaup-skeleton height="1rem" width="150px" />
    </div>
    <div class="card-footer text-center d-flex justify-content-center">
       <omegaup-skeleton height="1rem" width="120px" />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Skeleton from '../common/Skeleton.vue';

@Component({
  components: {
    'omegaup-skeleton': Skeleton,
  },
})
export default class SchoolOfTheMonthSkeleton extends Vue {}
</script>

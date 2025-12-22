<template>
  <div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center" :class="`card-header-${category}`">
       <omegaup-skeleton height="24px" width="150px" />
       <omegaup-skeleton circle height="16px" width="16px" />
    </div>
    <div class="card-body d-flex flex-column justify-content-center text-center align-items-center">
       <!-- Avatar -->
       <omegaup-skeleton height="80px" width="80px" class="mb-3" />
       
       <!-- Username -->
       <omegaup-skeleton height="1.25rem" width="120px" class="mb-2" />
       
       <!-- Name -->
       <omegaup-skeleton height="1rem" width="180px" class="mb-2" />
       
       <!-- School -->
       <omegaup-skeleton height="0.9rem" width="150px" class="mb-1" />
       
       <!-- Location -->
       <omegaup-skeleton height="0.9rem" width="100px" />
    </div>
    <div class="card-footer d-flex justify-content-center">
       <omegaup-skeleton height="1rem" width="120px" />
    </div>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import Skeleton from '../common/Skeleton.vue';

@Component({
  components: {
    'omegaup-skeleton': Skeleton,
  },
})
export default class CoderOfTheMonthSkeleton extends Vue {
  @Prop({ default: 'all' }) category!: string;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

// Mimic the dynamic header colors for skeleton container if needed, 
// or just rely on the parent styles if they bleed. 
// For now, we keep the class for potential styling hooks.
.card-header-female {
  // We can add a subtle pink tint here if we want the skeleton to hint at the category
}
</style>

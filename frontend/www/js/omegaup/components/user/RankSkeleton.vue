<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
       <omegaup-skeleton height="24px" width="200px" />
       <omegaup-skeleton circle height="20px" width="20px" />
    </div>
    
    <div v-if="!isIndex" class="card-body form-row">
       <!-- Search Inputs Placeholder -->
       <div class="col col-md-3 pl-0 pr-2">
         <omegaup-skeleton height="38px" />
       </div>
       <div class="col-4 col-md-2 mr-0 mr-md-2">
         <omegaup-skeleton height="38px" />
       </div>
       <div class="col-12 col-md-5 mt-2 mt-md-0">
         <omegaup-skeleton height="38px" />
       </div>
    </div>

    <!-- Table Header Skeleton -->
    <table class="table mb-0 table-responsive-sm">
      <thead>
        <tr>
          <th class="pl-4 column-width"><omegaup-skeleton height="1.5rem" width="20px"/></th>
          <th><omegaup-skeleton height="1.5rem" width="150px"/></th>
          <th class="text-right"><omegaup-skeleton height="1.5rem" width="80px"/></th>
          <th v-if="!isIndex" class="text-right pr-4"><omegaup-skeleton height="1.5rem" width="80px"/></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="i in 5" :key="i">
           <td class="pl-4"><omegaup-skeleton height="1rem" width="20px"/></td>
           <td>
             <div class="d-flex align-items-center">
               <omegaup-skeleton circle height="16px" width="16px" class="mr-2"/>
               <omegaup-skeleton height="1rem" width="120px"/>
             </div>
             <omegaup-skeleton height="0.8rem" width="80px" class="mt-1 ml-4"/>
           </td>
           <td class="text-right"><omegaup-skeleton height="1rem" width="60px"/></td>
           <td v-if="!isIndex" class="text-right pr-4"><omegaup-skeleton height="1rem" width="40px"/></td>
        </tr>
      </tbody>
    </table>

    <div class="card-footer">
       <div v-if="isIndex">
          <omegaup-skeleton height="1rem" width="100px" />
       </div>
       <div v-else class="d-flex justify-content-center">
          <omegaup-skeleton height="38px" width="300px" />
       </div>
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
export default class UserRankSkeleton extends Vue {
  @Prop({ default: false }) isIndex!: boolean;
}
</script>

<style scoped>
.column-width {
  max-width: 4rem;
}
</style>

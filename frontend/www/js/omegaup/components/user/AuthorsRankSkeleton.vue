<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
       <omegaup-skeleton height="24px" width="200px" />
    </div>
    
    <div class="card-body form-row">
       <div class="col col-md-4 pl-0 pr-2">
          <omegaup-skeleton height="38px" />
       </div>
       <div class="col-4 col-md-2">
          <omegaup-skeleton height="38px" />
       </div>
    </div>

    <table class="table mb-0">
      <thead>
        <tr>
          <th class="text-center"><omegaup-skeleton height="1.5rem" width="20px"/></th>
          <th class="text-center"><omegaup-skeleton height="1.5rem" width="150px"/></th>
          <th class="text-center"><omegaup-skeleton height="1.5rem" width="80px"/></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="i in 5" :key="i">
           <td class="text-center"><omegaup-skeleton height="1rem" width="20px"/></td>
           <td class="text-center">
              <div class="d-flex flex-column align-items-center">
                 <div class="d-flex align-items-center mb-1">
                    <omegaup-skeleton circle height="16px" width="16px" class="mr-2"/>
                    <omegaup-skeleton height="1rem" width="100px"/>
                 </div>
                 <omegaup-skeleton height="0.8rem" width="120px"/>
              </div>
           </td>
           <td class="text-center"><omegaup-skeleton height="1rem" width="60px"/></td>
           <td></td>
        </tr>
      </tbody>
    </table>

    <div class="card-footer d-flex justify-content-center">
       <omegaup-skeleton height="38px" width="300px" />
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
export default class AuthorsRankSkeleton extends Vue {}
</script>

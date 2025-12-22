<template>
  <div>
    <div class="card">
      <div class="card-header">
         <omegaup-skeleton height="1.5rem" width="200px" />
      </div>
      <div class="card-body px-2 px-sm-4">
        <!-- Search and Actions Skeleton -->
        <div class="row align-items-center mb-3">
          <div class="col-9 col-lg-6">
             <omegaup-skeleton height="38px" />
          </div>
          <div class="col-3">
             <omegaup-skeleton height="38px" width="80px" />
          </div>
        </div>
        
        <div class="form-row mb-3">
           <omegaup-skeleton height="20px" width="150px" />
        </div>
        
        <div class="row mt-3">
           <div class="col-8 col-md-6">
              <omegaup-skeleton height="38px" />
           </div>
           <div class="col px-0">
              <omegaup-skeleton height="38px" width="100px" />
           </div>
        </div>
      </div>
      
      <!-- Table Skeleton -->
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th scope="col" style="width: 5%"></th>
              <th scope="col" class="text-center"><omegaup-skeleton height="1rem" width="30px" /></th>
              <th scope="col" class="text-center"><omegaup-skeleton height="1rem" width="150px" /></th>
              <th scope="col" class="text-center"><omegaup-skeleton height="1rem" width="40px" /></th>
              <th scope="col" class="text-center"><omegaup-skeleton height="1rem" width="80px" /></th>
              <th scope="col" class="text-center"><omegaup-skeleton height="1rem" width="50px" /></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="i in 5" :key="i">
              <td class="align-middle"><omegaup-skeleton width="20px" height="20px" /></td>
              <td class="text-right align-middle"><omegaup-skeleton width="40px" /></td>
              <td class="align-middle">
                 <div class="d-flex align-items-center">
                    <omegaup-skeleton width="200px" height="1.2rem"/>
                    <omegaup-skeleton class="ml-2" width="20px" height="20px" circle/>
                 </div>
              </td>
              <td class="text-center align-middle"><omegaup-skeleton width="20px" height="20px" class="mx-auto" /></td>
              <td class="text-center align-middle"><omegaup-skeleton width="20px" height="20px" class="mx-auto" /></td>
              <td class="text-center align-middle"><omegaup-skeleton width="30px" height="30px" class="mx-auto" /></td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <div class="card-footer">
         <omegaup-skeleton height="38px" width="300px" class="mx-auto" />
      </div>
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
export default class MineSkeleton extends Vue {}
</script>

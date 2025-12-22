<template>
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
       <omegaup-skeleton height="24px" width="250px" />
       <omegaup-skeleton circle height="20px" width="20px" />
    </div>

    <table class="table mb-0">
      <thead>
        <tr>
          <th class="text-center"><omegaup-skeleton height="1.5rem" width="20px"/></th>
          <th class="text-center"><omegaup-skeleton height="1.5rem" width="150px"/></th>
          <th class="text-center"><omegaup-skeleton height="1.5rem" width="80px"/></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="i in 5" :key="i">
          <td class="text-center"><omegaup-skeleton height="1rem" width="20px"/></td>
          <td class="text-center d-flex justify-content-center align-items-center">
             <omegaup-skeleton circle height="16px" width="16px" class="mr-2"/>
             <omegaup-skeleton height="1rem" width="180px"/>
          </td>
          <td class="text-center"><omegaup-skeleton height="1rem" width="60px"/></td>
        </tr>
      </tbody>
    </table>

    <div class="card-footer">
       <div v-if="showHeader">
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
export default class SchoolRankSkeleton extends Vue {
  @Prop({ default: false }) showHeader!: boolean;
}
</script>

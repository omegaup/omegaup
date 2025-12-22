<template>
  <div class="card">
    <div class="table-responsive mb-0">
      <table class="table">
        <thead>
          <tr>
            <!-- ID -->
            <th scope="col" style="width: 5%">
              <omegaup-skeleton height="1.5rem" />
            </th>
            <!-- Title -->
            <th scope="col" style="width: 35%">
              <omegaup-skeleton height="1.5rem" />
            </th>
            <!-- Quality, Difficulty, Ratio, Score, Points -->
            <th scope="col" v-for="n in 5" :key="n" style="width: 12%">
              <omegaup-skeleton height="1.5rem" />
            </th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="row in 10" :key="row">
            <!-- ID -->
            <td><omegaup-skeleton width="80%" /></td>
            <!-- Title -->
            <td><omegaup-skeleton width="90%" /></td>
            <!-- Stats -->
            <td v-for="col in 5" :key="col">
              <omegaup-skeleton width="60%" class="mx-auto" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import Skeleton from '../common/Skeleton.vue';

@Component({
  components: {
    'omegaup-skeleton': Skeleton,
  },
})
export default class ProblemListSkeleton extends Vue {}
</script>

<style lang="scss" scoped>
.card {
  border-top: none;
  border-radius: 0rem 0rem 0.25rem 0.25rem;
}
table {
  border-collapse: separate;
  border-spacing: 0;
}
thead tr th {
  border: none;
}
</style>

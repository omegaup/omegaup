<template>
  <div class="container-fluid p-0 mt-0">
    <div class="row">
      <!-- Sidebar Skeleton -->
      <div class="col-md-3 col-lg-3">
        <div class="card">
          <div class="card-body text-center">
            <!-- Avatar -->
            <div class="mx-auto mb-3" style="width: 100px">
              <omegaup-skeleton circle height="100px" width="100px" />
            </div>
            <!-- Name -->
            <omegaup-skeleton height="1.5rem" class="mb-2 mx-auto" width="80%" />
            <!-- Rank / Country -->
            <omegaup-skeleton height="1rem" width="60%" class="mb-4 mx-auto" />

            <!-- Sidebar Items -->
            <div class="d-flex flex-column align-items-center">
              <div v-for="i in 5" :key="i" class="w-100 mb-2">
                <omegaup-skeleton height="2.5rem" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Content Skeleton -->
      <div class="col-md-9 col-lg-9">
        <div class="card">
          <div class="card-header">
            <omegaup-skeleton height="2rem" width="30%" />
          </div>
          <div class="card-body">
            <!-- Mimic generic form/grid layout -->
            <div class="row">
              <div class="col-md-6 mb-4" v-for="j in 4" :key="j">
                <omegaup-skeleton height="1.2rem" class="mb-2" width="40%" />
                <omegaup-skeleton height="2.5rem" width="100%" />
              </div>
            </div>
            <omegaup-skeleton height="8rem" class="mt-3" />
          </div>
        </div>
      </div>
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
export default class ProfileSkeleton extends Vue {}
</script>

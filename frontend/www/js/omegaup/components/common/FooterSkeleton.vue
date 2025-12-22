<template>
  <footer class="common-footer text-center mt-5">
    <div class="container-xl">
      <div class="footer-navigation d-lg-flex align-items-start py-5 m-auto">
        <!-- Brand / Logo -->
        <div class="footer-brand mb-4 mb-lg-0 max-width-logo mx-auto">
          <div class="d-block mx-auto mb-1 mt-n6" style="width: 120px">
             <omegaup-skeleton height="40px" width="120px" />
          </div>
          <div class="mx-auto mt-2">
            <omegaup-skeleton height="1rem" width="150px" />
          </div>
        </div>

        <!-- Columns (Contact, Site, Sponsors, etc) -->
        <!-- We can simulate 4 generic columns -->
        <div 
          v-for="i in 4" 
          :key="i"
          class="footer-list-section w-50 mb-4 mb-lg-0 mx-auto"
        >
          <div class="column-title mb-3 d-flex justify-content-center">
             <omegaup-skeleton height="1.5rem" width="100px" />
          </div>
          <ul class="d-flex flex-column align-items-center">
            <li v-for="j in 3" :key="j" class="mt-2 w-100 d-flex justify-content-center">
              <omegaup-skeleton height="1rem" width="80px" />
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <!-- Copyright bar -->
    <div class="copy mt-3">
      <div class="container-xl py-3 d-flex justify-content-center">
        <omegaup-skeleton height="1rem" width="300px" />
      </div>
    </div>
  </footer>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Skeleton from './Skeleton.vue';

@Component({
  components: {
    'omegaup-skeleton': Skeleton,
  },
})
export default class FooterSkeleton extends Vue {}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.common-footer {
  background-color: $omegaup-primary--darker;
  color: $omegaup-white;
  flex-grow: 1;
  
  .copy {
    background-color: $omegaup-primary--darkest;
  }
}

.footer-navigation {
   /* Mimic the flex layout from original footer */
   .footer-brand {
      max-width: 200px;
      order: -3;
      @media only screen and (max-width: 991px) {
        max-width: 100%;
      }
   }
}
</style>

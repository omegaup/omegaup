<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-color fixed-top p-0 text-right">
      <div class="container-xl pl-0 pl-xl-3">
        <!-- Brand / Logo -->
        <a class="navbar-brand p-3 mr-0 mr-sm-3" href="#">
          <omegaup-skeleton height="20px" width="100px" />
        </a>

        <!-- Right Side (User/Toggle) -->
        <div class="d-inline-flex d-flex-row order-lg-1 ml-auto align-items-center">
           <!-- Placeholder for Login Text or User Info -->
           <div class="d-none d-lg-block mr-3">
              <omegaup-skeleton height="1.5rem" width="80px" />
           </div>
           
           <!-- Mobile Toggler Placeholder -->
           <div class="d-lg-none mr-2">
               <omegaup-skeleton height="30px" width="30px" />
           </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script lang="ts">
import { Component, Vue } from 'vue-property-decorator';
import Skeleton from './Skeleton.vue';

@Component({
  components: {
    'omegaup-skeleton': Skeleton,
  },
})
export default class NavbarSkeleton extends Vue {}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.navbar-color {
  background-color: var(--header-primary-color);
}
</style>

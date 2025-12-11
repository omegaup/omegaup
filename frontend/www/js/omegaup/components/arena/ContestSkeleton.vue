<template>
  <div class="card-body">
    <div class="container">
      <div class="row p-1 flex-column flex-sm-row align-items-center">
        <!-- Contest Title Column -->
        <div class="col-md-5 col-sm-12 p-1 text-center">
          <div class="skeleton-line skeleton-title mb-2"></div>
          <div class="skeleton-line skeleton-subtitle"></div>
        </div>
        <!-- Organizer Column -->
        <div class="col-md-3 col-sm-12 p-1 text-center">
          <div class="skeleton-line skeleton-organizer"></div>
        </div>
        <!-- Button Column -->
        <div class="col-md-4 col-sm-12 p-1 text-center">
          <div class="skeleton-button mb-2"></div>
          <div class="skeleton-line skeleton-status"></div>
        </div>
      </div>
      <div class="row p-1 flex-column flex-sm-row align-items-center">
        <!-- Date/Time Info -->
        <div class="col-12 text-center">
          <div class="skeleton-line skeleton-date"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';

@Component
class ArenaContestSkeleton extends Vue {}

export default ArenaContestSkeleton;
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.skeleton-line {
  background: var(
    --arena-submissions-list-skeletonloader-initial-background-color
  );
  border-radius: 4px;
  animation: loading 1.5s infinite;
}

.skeleton-title {
  height: 20px;
  width: 80%;
  margin: 0 auto;
}

.skeleton-subtitle {
  height: 14px;
  width: 60%;
  margin: 0 auto;
}

.skeleton-organizer {
  height: 16px;
  width: 70%;
  margin: 0 auto;
}

.skeleton-status {
  height: 14px;
  width: 50%;
  margin: 0 auto;
}

.skeleton-date {
  height: 16px;
  width: 65%;
  margin: 0 auto;
}

.skeleton-button {
  height: 38px;
  width: 120px;
  border-radius: 6px;
  margin: 0 auto;
  background: var(
    --arena-submissions-list-skeletonloader-initial-background-color
  );
  animation: loading 1.5s infinite;
}

@keyframes loading {
  0% {
    background: var(
      --arena-submissions-list-skeletonloader-initial-background-color
    );
  }
  50% {
    background: var(
      --arena-submissions-list-skeletonloader-final-background-color
    );
  }
  100% {
    background: var(
      --arena-submissions-list-skeletonloader-initial-background-color
    );
  }
}
</style>

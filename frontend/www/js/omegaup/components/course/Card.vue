<template>
  <div class="col mb-3">
    <div class="card">
      <div class="row no-gutters">
        <div class="col-sm-2 course-bg"></div>
        <div class="col-sm-10">
          <div class="card-body">
            <h5 class="card-title">Nombre del curso</h5>
            <p class="card-text">This is a longer card with supporting text.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.row.no-gutters {
  background: $omegaup-white;

  .course-bg {
    background: red;
  }
}
</style>

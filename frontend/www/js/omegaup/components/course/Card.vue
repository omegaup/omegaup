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

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';

@Component
export default class CourseCard extends Vue {
  @Prop() course!: types.StudentCourses; //TODO: Hacer este un tipo individual.
}

</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

.row.no-gutters {
  background: $omegaup-white;

  .course-bg {
    background: red;
  }
}
</style>

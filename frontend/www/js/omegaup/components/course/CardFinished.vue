<template>
  <div class="col mb-3">
    <div class="card">
      <div class="row no-gutters">
        <div class="col-sm-2 enrolled-course-card"></div>
        <div class="col-sm-10">
          <div class="card-body d-flex flex-column h-100">
            <h5 class="card-title mb-0">{{ course.name }}</h5>
            <div class="h-100 d-flex flex-column justify-content-between">
              <div class="text-center course-star">⭐</div>
              <div class="text-center">
                <a
                  class="btn btn-primary text-white"
                  role="button"
                  :href="`/course/${course.alias}/`"
                  >{{ T.wordsSeeCourse }}</a
                >
              </div>
            </div>
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
import * as ui from '../../ui';

import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class CourseCardFinished extends Vue {
  @Prop() course!: types.CourseCardFinished;

  T = T;
  ui = ui;
}
</script>

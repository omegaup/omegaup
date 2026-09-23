<template>
  <div data-course-clone>
    <form class="form" @submit.prevent="$emit('clone', alias, name, startTime)">
      <div class="row">
        <div class="mb-3 col-md-6">
          <label
            >{{ T.wordsName }}
            <input v-model="name" class="form-control" type="text"
          /></label>
        </div>
        <div class="mb-3 col-md-3">
          <label
            >{{ T.courseNewFormShortTitleAlias }}
            <font-awesome-icon
              :title="T.courseNewFormShortTitleAliasDesc"
              icon="info-circle" />
            <input v-model="alias" class="form-control" type="text"
          /></label>
        </div>
        <div class="mb-3 col-md-3">
          <label
            >{{ T.courseNewFormStartDate }}
            <font-awesome-icon
              :title="T.courseNewFormStartDateDesc"
              icon="info-circle" />
            <omegaup-datepicker v-model="startTime"></omegaup-datepicker
          ></label>
        </div>
      </div>
      <div class="mb-3 text-end">
        <button class="btn btn-primary" type="submit">
          {{ T.wordsCloneCourse }}
        </button>
      </div>
    </form>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import DatePicker from '../DatePicker.vue';

import {
  FontAwesomeIcon,
  FontAwesomeLayers,
  FontAwesomeLayersText,
} from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { library } from '@fortawesome/fontawesome-svg-core';
library.add(fas);

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
    'font-awesome-icon': FontAwesomeIcon,
    'font-awesome-layers': FontAwesomeLayers,
    'font-awesome-layers-text': FontAwesomeLayersText,
  },
})
export default class CourseClone extends Vue {
  @Prop() initialAlias!: string;
  @Prop() initialName!: string;

  T = T;
  alias = this.initialAlias;
  startTime = new Date();
  name = this.initialName;
}
</script>

<style scoped>
.mb-3 > label {
  width: 100%;
}

.faux-label {
  font-weight: bold;
}
</style>

<template>
  <div class="omegaup-course-details panel-primary panel">
    <div class="panel-heading"
         v-if="!update">
      <h3 class="panel-title">{{ T.courseNew }}</h3>
    </div>
    <div class="panel-body">
      <form class="form"
            v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-8">
            <label>{{ T.wordsName }} <input class="form-control name"
                   type="text"
                   v-model="name"></label>
          </div>
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormShortTitle_alias_ }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormShortTitle_alias_Desc"></span> <input class=
                  "form-control alias"
                   type="text"
                   v-bind:disabled="update"
                   v-model="alias"></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormStartDate }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormStartDateDesc"></span> <omegaup-datepicker v-model=
                  "startTime"></omegaup-datepicker></label>
          </div>
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormEndDate }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormEndDateDesc"></span> <omegaup-datepicker v-model=
                  "finishTime"></omegaup-datepicker></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label">{{ T.courseNewFormShowScoreboard }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormShowScoreboardDesc"></span></span>
            <div class="form-control container-fluid">
              <label class="radio-inline"><input type="radio"
                     v-bind:value="true"
                     v-model="showScoreboard">{{ T.wordsYes }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-bind:value="false"
                     v-model="showScoreboard">{{ T.wordsNo }}</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label>{{ T.profileSchool }} <input autocomplete="off"
                   class="form-control typeahead school"
                   type="text"
                   v-model="school_name"
                   v-on:change="onChange"><input class="school_id"
                   type="hidden"
                   v-model="school_id"></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label">{{ T.courseNewFormBasicInformationRequired }}
            <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormBasicInformationRequiredDesc"></span></span>
            <div class="form-control container-fluid">
              <label class="radio-inline"><input type="radio"
                     v-bind:value="true"
                     v-model="basic_information_required">{{ T.wordsYes }}</label> <label class=
                     "radio-inline"><input type="radio"
                     v-bind:value="false"
                     v-model="basic_information_required">{{ T.wordsNo }}</label>
            </div>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label">{{ T.courseNewFormUserInformationRequired }}
            <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormUserInformationRequiredDesc"></span></span>
                  <select class="form-control"
                 v-model="requests_user_information">
              <option value="no">
                {{ T.wordsNo }}
              </option>
              <option value="optional">
                {{ T.wordsOptional }}
              </option>
              <option value="required">
                {{ T.wordsRequired }}
              </option>
            </select>
          </div>
          <div class="row">
            <div class="form-group container-fluid">
              <label>{{ T.courseNewFormDescription }}
              <textarea class="form-control"
                        cols="30"
                        rows="5"
                        v-model="description"></textarea></label>
            </div>
          </div>
          <div class="form-group pull-right">
            <button class="btn btn-primary submit"
                 type="submit">
            <template v-if="update">
              {{ T.courseNewFormUpdateCourse }}
            </template>
            <template v-else="">
              {{ T.courseNewFormScheduleCourse }}
            </template></button> <button class="btn btn-secondary"
                 type="reset"
                 v-on:click.prevent="onCancel">{{ T.wordsCancel }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
.omegaup-course-details .form-group>label {
  width: 100%;
}
.omegaup-course-details .faux-label {
  font-weight: bold;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit } from 'vue-property-decorator';
import omegaup from '../../api.js';
import { T } from '../../omegaup.js';
import UI from '../../ui.js';
import DatePicker from '../DatePicker.vue';

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
  },
})
export default class CourseDetails extends Vue {
  @Prop() update!: boolean;
  @Prop() course!: omegaup.Course;

  UI = UI;
  T = T;
  alias = this.course.alias || '';
  description = this.course.description || '';
  finishTime = this.course.finish_time || new Date();
  showScoreboard = !!this.course.show_scoreboard;
  startTime = this.course.start_time || new Date();
  name = this.course.name || '';
  school_id?: number = this.course.school_id;
  school_name = this.course.school_name || '';
  basic_information_required = !!this.course.basic_information_required;
  requests_user_information = this.course.requests_user_information || 'no';

  mounted(): void {
    let self = this;
    this.UI.schoolTypeahead($('input.typeahead', self.$el), function(
      event: Event,
      item: any,
    ) {
      self.school_name = item.value;
      self.school_id = item.id;
    });
  }

  @Watch('course')
  onCourseChange() {
    this.reset();
  }

  reset(): void {
    this.alias = this.course.alias || '';
    this.description = this.course.description || '';
    this.finishTime = this.course.finish_time || new Date();
    this.showScoreboard = !!this.course.show_scoreboard;
    this.startTime = this.course.start_time || new Date();
    this.name = this.course.name || '';
    this.school_id = this.course.school_id;
    this.school_name = this.course.school_name || '';
    this.basic_information_required = !!this.course.basic_information_required;
    this.requests_user_information =
      this.course.requests_user_information || 'no';
  }

  onSubmit(): void {
    this.$emit('submit', this);
  }

  @Emit('cancel')
  onCancel(): void {
    this.reset();
  }

  onChange(): void {
    if (this.course.school_id === this.school_id) {
      this.school_id = undefined;
    } else {
      this.course.school_id = this.school_id;
    }
  }
}

</script>

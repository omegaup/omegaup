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
            <label>{{ T.wordsName }} <input class="form-control"
                   type="text"
                   v-model="name"></label>
          </div>
          <div class="form-group col-md-4">
            <label>{{ T.courseNewFormShortTitle_alias_ }} <span aria-hidden="true"
                  class="glyphicon glyphicon-info-sign"
                  data-placement="top"
                  data-toggle="tooltip"
                  v-bind:title="T.courseNewFormShortTitle_alias_Desc"></span> <input class=
                  "form-control"
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
                  v-bind:title="T.courseNewFormEndDateDesc"></span> <omegaup-datepicker v-model=
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
                     v-model="showScoreboard"
                     value="1">{{ T.wordsYes }}</label> <label class="radio-inline"><input type=
                     "radio"
                     v-model="showScoreboard"
                     value="0">{{ T.wordsNo }}</label>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-12">
            <label>{{ T.profileSchool }} <input autocomplete="off"
                   class="form-control typeahead"
                   type="text"
                   v-model="school_name"><input type="hidden"
                   v-model="school_id"></label>
          </div>
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
          <button class="btn btn-primary"
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
      </form>
    </div>
  </div>
</template>

<script>
import UI from '../../ui.js';
import DatePicker from '../DatePicker.vue';

export default {
  props: {
    T: Object,
    update: Boolean,
    course: Object,
  },
  data: function() {
    return {
      alias: this.course.alias,
      description: this.course.description,
      finishTime: this.course.finish_time || new Date(),
      showScoreboard: this.course.show_scoreboard || 0,
      startTime: this.course.start_time || new Date(),
      name: this.course.name,
      school_id: this.course.school_id,
      school_name: this.course.school_name
    };
  },
  mounted: function() {
    let self = this;
    UI.schoolTypeahead($('input.typeahead', self.$el), function(event, item) {
      self.school_name = item.value;
      self.school_id = item.id;
    });
  },
  watch: {
    course: function(val) { this.reset();},
  },
  methods: {
    reset: function() {
      this.alias = this.course.alias;
      this.description = this.course.description;
      this.finishTime = this.course.finish_time || new Date();
      this.showScoreboard = this.course.show_scoreboard || 0;
      this.startTime = this.course.start_time || new Date();
      this.name = this.course.name;
      this.school_id = this.course.school_id;
      this.school_name = this.course.school_name;
    },
    onSubmit: function() { this.$emit('submit', this);},
    onCancel: function() {
      this.reset();
      this.$emit('cancel');
    },
  },
  components: {
    'omegaup-datepicker': DatePicker,
  },
};
</script>

<style>
.omegaup-course-details .form-group>label {
  width: 100%;
}
.omegaup-course-details .faux-label {
  font-weight: bold;
}
</style>

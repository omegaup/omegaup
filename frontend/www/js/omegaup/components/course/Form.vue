<template>
  <div class="omegaup-course-details card">
    <div class="card-header" v-if="!update">
      <h3 class="card-title">{{ T.courseNew }}</h3>
    </div>
    <div class="card-body">
      <form class="form" data-course-form v-on:submit.prevent="onSubmit">
        <div class="row">
          <div class="form-group col-md-4">
            <label class="faux-label"
              >{{ T.wordsName }}
              <input
                class="form-control"
                data-course-new-name
                type="text"
                v-model="name"
                required="required"
            /></label>
          </div>
          <div class="form-group col-md-4">
            <label class="faux-label"
              >{{ T.courseNewFormShortTitle_alias_ }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormShortTitle_alias_Desc"
              ></span>
              <input
                class="form-control"
                type="text"
                data-course-new-alias
                v-bind:disabled="update"
                v-model="alias"
                required="required"
            /></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormShowScoreboard }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormShowScoreboardDesc"
              ></span
            ></span>
            <div class="form-control container-fluid">
              <label class="radio-inline"
                ><input
                  type="radio"
                  name="show-scoreboard"
                  v-bind:value="true"
                  v-model="showScoreboard"
                />{{ T.wordsYes }}</label
              >
              <label class="radio-inline"
                ><input
                  type="radio"
                  name="show-scoreboard"
                  v-bind:value="false"
                  v-model="showScoreboard"
                />{{ T.wordsNo }}</label
              >
            </div>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label class="faux-label"
              >{{ T.courseNewFormStartDate }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormStartDateDesc"
              ></span>
              <omegaup-datepicker v-model="startTime"></omegaup-datepicker
            ></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormUnlimitedDuration }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormUnlimitedDurationDesc"
              ></span
            ></span>
            <div class="form-control container-fluid">
              <label class="radio-inline"
                ><input
                  type="radio"
                  v-bind:value="true"
                  v-model="unlimitedDuration"
                />{{ T.wordsYes }}</label
              >
              <label class="radio-inline"
                ><input
                  type="radio"
                  v-bind:value="false"
                  v-model="unlimitedDuration"
                />{{ T.wordsNo }}</label
              >
            </div>
          </div>
          <div class="form-group col-md-4">
            <label class="faux-label"
              >{{ T.courseNewFormEndDate }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormEndDateDesc"
              ></span>
              <omegaup-datepicker
                v-bind:enabled="!unlimitedDuration"
                v-model="finishTime"
              ></omegaup-datepicker
            ></label>
          </div>
        </div>
        <div class="row">
          <div class="form-group col-md-4">
            <label class="faux-label"
              >{{ T.profileSchool }}
              <input
                autocomplete="off"
                class="form-control typeahead school"
                type="text"
                v-model="school_name"
                v-on:change="onChange" /><input
                class="school_id"
                type="hidden"
                v-model="school_id"
            /></label>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormBasicInformationRequired }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormBasicInformationRequiredDesc"
              ></span
            ></span>
            <div class="form-control container-fluid">
              <label class="radio-inline"
                ><input
                  type="radio"
                  v-bind:value="true"
                  v-model="needs_basic_information"
                />{{ T.wordsYes }}</label
              >
              <label class="radio-inline"
                ><input
                  type="radio"
                  v-bind:value="false"
                  v-model="needs_basic_information"
                />{{ T.wordsNo }}</label
              >
            </div>
          </div>
          <div class="form-group col-md-4">
            <span class="faux-label"
              >{{ T.courseNewFormUserInformationRequired }}
              <span
                aria-hidden="true"
                class="glyphicon glyphicon-info-sign"
                data-placement="top"
                data-toggle="tooltip"
                v-bind:title="T.courseNewFormUserInformationRequiredDesc"
              ></span
            ></span>
            <select class="form-control" v-model="requests_user_information">
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
          <div class="form-group container-fluid">
            <label
              >{{ T.courseNewFormDescription }}
              <textarea
                class="form-control"
                cols="30"
                rows="5"
                v-model="description"
                required="required"
              ></textarea>
            </label>
          </div>
          <div class="form-group col-md-12 text-right">
            <button class="btn btn-primary mr-2 submit" type="submit">
              <template v-if="update">
                {{ T.courseNewFormUpdateCourse }}
              </template>
              <template v-else="">
                {{ T.courseNewFormScheduleCourse }}
              </template>
            </button>
            <button
              class="btn btn-secondary"
              type="reset"
              v-on:click.prevent="onCancel"
            >
              {{ T.wordsCancel }}
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<style>
.omegaup-course-details .form-group > label {
  width: 100%;
}
.omegaup-course-details .faux-label {
  font-weight: bold;
}

.twitter-typeahead {
  width: 100%;
}
</style>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import DatePicker from '../DatePicker.vue';

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
  },
})
export default class CourseDetails extends Vue {
  @Prop({ default: false }) update!: boolean;
  @Prop() course!: types.CourseDetails;

  T = T;
  alias = this.course.alias;
  description = this.course.description;
  finishTime = this.course.finish_time || new Date();
  showScoreboard = this.course.show_scoreboard;
  startTime = this.course.start_time;
  name = this.course.name;
  school_name = this.course.school_name;
  school_id = this.course.school_id;
  needs_basic_information = this.course.needs_basic_information;
  requests_user_information = this.course.requests_user_information;
  unlimitedDuration = this.course.finish_time === null;

  data(): { [name: string]: any } {
    return {
      school_id: this.course.school_id,
    };
  }

  mounted(): void {
    typeahead.schoolTypeahead(
      $('input.typeahead', this.$el),
      (event: Event, item: any) => {
        this.school_name = item.value;
        this.school_id = item.id;
      },
    );
  }

  reset(): void {
    this.alias = this.course.alias;
    this.description = this.course.description;
    this.finishTime = this.course.finish_time || new Date();
    this.showScoreboard = this.course.show_scoreboard;
    this.startTime = this.course.start_time;
    this.name = this.course.name;
    this.school_name = this.course.school_name;
    this.school_id = this.course.school_id;
    this.needs_basic_information = this.course.needs_basic_information;
    this.requests_user_information = this.course.requests_user_information;
    this.unlimitedDuration = this.course.finish_time === null;
  }

  onSubmit(): void {
    this.$emit('submit', this);
  }

  @Emit('emit-cancel')
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

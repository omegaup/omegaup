<template>
  <form role="form" class="card-body" @submit.prevent="onUpdateUserSchools">
    <div class="form-group">
      <label>{{ T.profileSchool }}</label>
      <omegaup-autocomplete
        v-model="school"
        class="form-control"
        :init="
          (el) =>
            typeahead.schoolTypeahead(el, (event, val) => {
              school = val.value;
              schoolId = val.id;
            })
        "
      ></omegaup-autocomplete>
      <input v-model="schoolId" type="hidden" />
    </div>
    <div class="form-group">
      <label>{{ T.userEditSchoolGrade }}</label>
      <select v-model="scholarDegree" class="form-control">
        <option value="none">{{ T.userEditNone }}</option>
        <option value="early_childhood">
          {{ T.userEditEarlyChildhood }}
        </option>
        <option value="pre_primary">{{ T.userEditPrePrimary }}</option>
        <option value="primary">{{ T.userEditPrimary }}</option>
        <option value="lower_secondary">
          {{ T.userEditLowerSecondary }}
        </option>
        <option value="upper_secondary">
          {{ T.userEditUpperSecondary }}
        </option>
        <option value="post_secondary">{{ T.userEditPostSecondary }}</option>
        <option value="tertiary">{{ T.userEditTertiary }}</option>
        <option value="bachelors">{{ T.userEditBachelors }}</option>
        <option value="master">{{ T.userEditMaster }}</option>
        <option value="doctorate">{{ T.userEditDoctorate }}</option>
      </select>
    </div>
    <div class="form-group">
      <label>{{ T.userEditManageSchoolsUserCurrentlyEnrolled }}</label>
      <omegaup-radio-switch
        :value.sync="isCurrentlyEnrolled"
        :selected-value="isCurrentlyEnrolled"
      ></omegaup-radio-switch>
    </div>
    <div class="form-group">
      <label>{{ T.userEditGraduationDate }}</label>
      <omegaup-datepicker
        v-model="graduationDate"
        :required="false"
        :enabled="!isCurrentlyEnrolled"
      ></omegaup-datepicker>
    </div>
    <div class="mt-3">
      <button type="submit" class="btn btn-primary mr-2">
        {{ T.wordsSaveChanges }}
      </button>
      <a href="/profile/" class="btn btn-cancel">{{ T.wordsCancel }}</a>
    </div>
  </form>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as typeahead from '../../typeahead';
import * as time from '../../time';
import Autocomplete from '../Autocomplete.vue';
import DatePicker from '../DatePicker.vue';
import OmegaupRadioSwitch from '../RadioSwitch.vue';

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
    'omegaup-autocomplete': Autocomplete,
    'omegaup-radio-switch': OmegaupRadioSwitch,
  },
})
export default class UserManageSchools extends Vue {
  @Prop() profile!: types.UserProfileInfo;

  T = T;
  typeahead = typeahead;
  graduationDate = this.profile.graduation_date
    ? time.convertLocalDateToGMTDate(this.profile.graduation_date)
    : new Date('');
  school = this.profile.school;
  schoolId = this.profile.school_id;
  scholarDegree = this.profile.scholar_degree;
  isCurrentlyEnrolled = !this.profile.graduation_date;

  onUpdateUserSchools(): void {
    this.$emit('update-user-schools', {
      graduation_date:
        this.isCurrentlyEnrolled || isNaN(this.graduationDate.getTime())
          ? null
          : this.graduationDate,
      school_id:
        this.schoolId === this.profile.school_id &&
        this.school !== this.profile.school
          ? null
          : this.schoolId,
      school_name: this.school,
      scholar_degree: this.scholarDegree,
    });
  }
}
</script>

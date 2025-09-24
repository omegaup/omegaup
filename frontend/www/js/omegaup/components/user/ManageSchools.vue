<template>
  <form role="form" class="card-body" @submit.prevent="onUpdateUserSchools">
    <div class="form-group">
      <label>{{ T.profileSchool }}</label>
      <omegaup-common-typeahead
        :existing-options="searchResultSchools"
        :options="searchResultSchools"
        :value.sync="school"
        data-school-name
        @update-existing-options="
          (query) => $emit('update-search-result-schools', query)
        "
      ></omegaup-common-typeahead>
    </div>
    <div class="form-group">
      <label>{{ T.userEditSchoolGrade }}</label>
      <select v-model="scholarDegree" class="form-control" data-school-grade>
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
    <div class="form-group" data-graduation-date>
      <label>{{ T.userEditGraduationDate }}</label>
      <omegaup-datepicker
        v-model="graduationDate"
        :required="false"
        :enabled="!isCurrentlyEnrolled"
      ></omegaup-datepicker>
    </div>
    <div class="mt-3">
      <button
        type="submit"
        class="btn btn-primary mr-2"
        data-save-school-changes
        :disabled="!hasChanges"
      >
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
import * as time from '../../time';
import common_Typeahead from '../common/Typeahead.vue';
import DatePicker from '../DatePicker.vue';
import OmegaupRadioSwitch from '../RadioSwitch.vue';

@Component({
  components: {
    'omegaup-datepicker': DatePicker,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-radio-switch': OmegaupRadioSwitch,
  },
})
export default class UserManageSchools extends Vue {
  @Prop() profile!: types.UserProfileInfo;
  @Prop() searchResultSchools!: types.SchoolListItem[];

  T = T;
  graduationDate = this.profile.graduation_date
    ? time.convertLocalDateToGMTDate(this.profile.graduation_date)
    : new Date('');
  school: null | types.SchoolListItem = this.searchResultSchools[0] ?? null;
  scholarDegree = this.profile.scholar_degree;
  isCurrentlyEnrolled = !this.profile.graduation_date;

  get hasChanges(): boolean {
    const currentSchoolId = this.school?.key ?? null;
    const currentSchoolName = this.school?.value ?? null;
    const hasSchoolChanges =
      currentSchoolId !== this.profile.school_id ||
      (currentSchoolName !== this.profile.school && currentSchoolId !== null);

    const graduationDateChanged =
      (this.isCurrentlyEnrolled && this.profile.graduation_date !== null) ||
      (!this.isCurrentlyEnrolled &&
        !isNaN(this.graduationDate.getTime()) &&
        this.graduationDate.toISOString().split('T')[0] !==
          (this.profile.graduation_date ?? ''));

    return (
      hasSchoolChanges ||
      this.scholarDegree !== this.profile.scholar_degree ||
      graduationDateChanged
    );
  }

  onUpdateUserSchools(): void {
    this.$emit('update-user-schools', {
      graduation_date:
        this.isCurrentlyEnrolled || isNaN(this.graduationDate.getTime())
          ? null
          : this.graduationDate,
      school_id:
        !this.school ||
        (this.school.key === this.profile.school_id &&
          this.school.value !== this.profile.school)
          ? null
          : this.school.key,
      school_name: this.school?.value,
      scholar_degree: this.scholarDegree,
    });
  }
}
</script>

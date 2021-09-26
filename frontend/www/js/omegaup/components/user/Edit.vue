<template>
  <div class="container-fluid p-0 mt-0" data-user-profile-root>
    <div class="row">
      <div class="col-md-2">
        <omegaup-user-maininfo :profile="profile" :data="data" :edit="true" />
      </div>
      <div class="col-md-10">
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">{{ T.userEditEditProfile }}</h3>
          </div>
          <form role="form" class="card-body" @submit.prevent="onUpdateUser">
            <div class="form-group">
              <label>{{ T.username }}</label>
              <input
                v-model="selectedProfileInfo.username"
                class="form-control"
              />
            </div>
            <div class="form-group">
              <label>{{ T.wordsName }}</label>
              <input v-model="selectedProfileInfo.name" class="form-control" />
            </div>
            <div class="form-group">
              <label>{{ T.userEditBirthDate }}</label>
              <font-awesome-icon
                :title="T.courseNewFormStartDateDesc"
                icon="info-circle"
              />
              <omegaup-datepicker
                v-model="selectedProfileInfo.birth_date"
              ></omegaup-datepicker>
            </div>
            <div class="form-group">
              <label>{{ T.wordsGender }}</label>
              <select
                v-model="selectedProfileInfo.gender"
                name="gender"
                class="form-control"
              >
                <option value="female">{{ T.wordsGenderFemale }}</option>
                <option value="male">{{ T.wordsGenderMale }}</option>
                <option value="other">{{ T.wordsGenderOther }}</option>
                <option value="decline">{{ T.wordsGenderDecline }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>{{ T.wordsCountry }}</label>
              <select
                v-model="selectedProfileInfo.country_id"
                name="country_id"
                class="form-control"
              >
                <option
                  v-for="country in countries"
                  :key="country.country_id"
                  :value="country.country_id"
                >
                  {{ country.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>
                {{ T.profileState }}
              </label>
              <select
                v-model="selectedProfileInfo.state_id"
                class="form-control"
              >
                <option
                  v-for="[code, state] in Object.entries(countryStates)"
                  :key="code"
                  :value="code.split('-')[1]"
                >
                  {{ state.name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label class="d-block">
                {{ T.profileSchool }}
                <omegaup-common-typeahead
                  class="w-100"
                  :existing-options="schools"
                  :value.sync="participant"
                  :max-results="10"
                  @update-existing-options="
                    (query) => $emit('update-search-result-school', query)
                  "
                />
                <!-- <omegaup-autocomplete
                  v-model="selectedProfileInfo.school"
                  class="form-control"
                  :init="
                    (el) =>
                      typeahead.schoolTypeahead(el, (event, item) => {
                        console.log('hola',item);
                        selectedProfileInfo.school = item.value;
                        selectedProfileInfo.school_id = item.id;
                      })
                  "
                ></omegaup-autocomplete>
                <input type="hidden" v-model="selectedProfileInfo.school_id" />-->
              </label>
            </div>
            <div class="form-group">
              <label for="locale" class="control-label">{{
                T.userEditLanguage
              }}</label>

              <select
                v-model="selectedProfileInfo.locale"
                name="locale"
                class="form-control"
              >
                <option value="es">{{ T.wordsSpanish }}</option>
                <option value="en">{{ T.wordsEnglish }}</option>
                <option value="pt">{{ T.wordsPortuguese }}</option>
                <option :v-if="!inProduction" value="pseudo">pseudo-loc</option>
                {/if} -->
              </select>
            </div>
            <div class="form-group">
              <label for="scholar_degree" class="control-label">{{
                T.userEditSchoolGrade
              }}</label>
              <select
                v-model="selectedProfileInfo.scholar_degree"
                name="scholar_degree"
                class="form-control"
              >
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
                <option value="post_secondary">
                  {{ T.userEditPostSecondary }}
                </option>
                <option value="tertiary">{{ T.userEditTertiary }}</option>
                <option value="bachelors">{{ T.userEditBachelors }}</option>
                <option value="master">{{ T.userEditMaster }}</option>
                <option value="doctorate">{{ T.userEditDoctorate }}</option>
              </select>
            </div>
            <div class="form-group">
              <label for="programming_language" class="control-label">{{
                T.userEditPreferredProgrammingLanguage
              }}</label>
              <select
                v-model="selectedProfileInfo.preferred_language"
                name="programming_language"
                class="form-control"
              >
                <option value=""></option>
                <option
                  v-for="[extension, name] in Object.entries(
                    programmingLanguages,
                  )"
                  :key="extension"
                  :value="extension"
                >
                  {{ name }}
                </option>
              </select>
            </div>
            <div class="form-group">
              <label>{{ T.userEditGraduationDate }}</label>
              <font-awesome-icon
                :title="T.courseNewFormStartDateDesc"
                icon="info-circle"
              />
              <omegaup-datepicker
                v-model="selectedProfileInfo.graduation_date"
              ></omegaup-datepicker>
            </div>

            <div class="form-group">
              <label class="control-label">{{ T.userEditProfileImage }}</label>
              <a
                href="http://www.gravatar.com"
                target="_blank"
                class="btn btn-link"
                >{{ T.userEditGravatar }} {{ selectedProfileInfo.email }}</a
              >
            </div>

            <div class="form-group">
              <span class="control-label">&nbsp;</span>
              <input
                v-model="selectedProfileInfo.is_private"
                type="checkbox"
                name="is_private"
                :checked="selectedProfileInfo.is_private"
              />
              <label for="is_private" style="display: inline">{{
                T.userEditPrivateProfile
              }}</label>
            </div>

            <div class="form-group">
              <span class="control-label">&nbsp;</span>
              <input
                v-model="selectedProfileInfo.hide_problem_tags"
                type="checkbox"
                name="hide_problem_tags"
                :checked="selectedProfileInfo.hide_problem_tags"
              />
              <label for="hide_problem_tags">{{
                T.userEditHideProblemTags
              }}</label>
            </div>

            <div class="col-md-offset-6 col-md-6 col-xs-12">
              <button
                type="submit"
                class="btn btn-primary col-xs-offset-1 col-xs-5"
              >
                {{ T.wordsSaveChanges }}
              </button>
              <a href="/profile" class="btn col-xs-5 btn-cancel">{{
                T.wordsCancel
              }}</a>
            </div>
          </form>
        </div>
        <omegaup-user-manage-identities
          :identities="identities"
          @add-identity="
            (username, password) =>
              this.$emit('add-identity', username, password)
          "
        />
        <form rol="form" class="card">
          <div class="card-header">
            <h3 class="card-title">{{ T.userEditChangePassword }}</h3>
          </div>
          <div class="card-body">
            <form
              class="form-horizontal"
              role="form"
              @submit.prevent="onUpdatePassword"
            >
              <div class="form-group">
                <label for="name" class="col-md-4 control-label">{{
                  T.userEditChangePasswordOldPassword
                }}</label>
                <div class="col-md-7">
                  <input
                    v-model="oldPassword"
                    name="name"
                    value=""
                    required
                    type="password"
                    size="30"
                    class="form-control"
                  />
                </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-4 control-label">{{
                  T.userEditChangePasswordNewPassword
                }}</label>
                <div class="col-md-7">
                  <input
                    v-model="newPassword1"
                    name="name"
                    value=""
                    required
                    type="password"
                    size="30"
                    class="form-control"
                  />
                </div>
              </div>
              <div class="form-group">
                <label for="name" class="col-md-4 control-label">{{
                  T.userEditChangePasswordRepeatNewPassword
                }}</label>
                <div class="col-md-7">
                  <input
                    v-model="newPassword2"
                    name="name"
                    value=""
                    required
                    type="password"
                    size="30"
                    class="form-control"
                  />
                </div>
              </div>

              <div class="col-md-offset-6 col-md-6 col-xs-12">
                <button
                  type="submit"
                  class="btn btn-primary col-xs-offset-1 col-xs-5"
                >
                  {{ T.wordsSaveChanges }}
                </button>
                <a href="/profile/" class="btn col-xs-5 btn-cancel">{{
                  T.wordsCancel
                }}</a>
              </div>
            </form>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import user_MainInfo from './MainInfo.vue';
import DatePicker from '../DatePicker.vue';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import user_ManageIdentities from './ManageIdentities.vue';
import * as iso3166 from '@/third_party/js/iso-3166-2.js/iso3166.min.js';
import * as typeahead from '../../typeahead';
import common_Typeahead from '../common/Typeahead.vue';
import { types } from '../../api_types';
@Component({
  components: {
    'font-awesome-icon': FontAwesomeIcon,
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-datepicker': DatePicker,
    'omegaup-user-manage-identities': user_ManageIdentities,
    'omegaup-user-maininfo': user_MainInfo,
  },
})
export default class UserProfileEdit extends Vue {
  @Prop() data!: types.UserProfileEditDetailsPayload | null;
  @Prop() profile!: types.UserProfileInfo;
  @Prop() identities!: types.Identity[];
  @Prop() schools!: types.School[];
  @Prop() inProduction!: boolean;

  T = T;
  typeahead = typeahead;
  countries = this.data?.countries;
  programmingLanguages = this.profile.programming_languages;
  oldPassword = '';
  newPassword1 = '';
  newPassword2 = '';

  selectedProfileInfo = Object.assign(
    {
      username: '',
      classname: '',
      name: '',
      birth_date: new Date(),
      gender: '',
      school: '',
      school_id: 0,
      state: '',
      country: 'México',
      country_id: 'MX',
      state_id: '',
      hide_problem_tags: false,
      is_own_profile: true,
      is_private: false,
      preferred_language: '',
      graduation_date: new Date(),
      scholar_degree: '',
      verified: true,
      programming_languages: {},
      rankinfo: '',
    } as types.UserProfileInfo,
    this.profile,
  );

  get countryStates(): iso3166.Subdivisions {
    const countryId = this.selectedProfileInfo.country_id || 'MX';
    const countrySelected = iso3166.country(countryId);
    let subdivisions = Object.entries(countrySelected.sub)
      .sort((a, b) => Intl.Collator().compare(a[0], b[0]))
      .reduce((r, [code, name]: any) => ({ ...r, [code]: name }), {});
    return subdivisions as iso3166.Subdivisions;
  }

  onUpdateUser(): void {
    this.$emit('update-user', this.selectedProfileInfo);
  }
  onUpdatePassword(): void {
    this.$emit(
      'update-password',
      this.oldPassword,
      this.newPassword1,
      this.newPassword2,
    );
  }
}
</script>

<style lang="scss" scoped>
a:hover {
  cursor: pointer;
}
</style>

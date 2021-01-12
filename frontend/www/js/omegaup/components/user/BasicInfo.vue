<template>
  <div class="card-body">
    <div class="fields-border">
      <div class="form-group row padding-field">
        <div class="col-sm-3">
          <strong>{{ T.profileUsername }}</strong>
        </div>
        <div class="col-sm-9 field-data">
          https://omegaup.com/profile/<strong
            ><omegaup-user-username
              :classname="profile.classname"
              :username="profile.username"
            ></omegaup-user-username></strong
          >/
        </div>
      </div>
      <div class="form-group row padding-field">
        <div class="col-sm-3">
          <strong>{{ T.profile }}</strong>
        </div>
        <div class="col-sm-9 field-data">
          {{ profile.name }}
        </div>
      </div>

      <div v-if="profile.email">
        <div class="form-group row padding-field">
          <div class="col-sm-3">
            <strong>{{ T.profileEmail }}</strong>
          </div>
          <div class="col-sm-9 field-data">
            Primary: <strong data-email> {{ profile.email }}</strong
            >&nbsp;
          </div>
        </div>

        <div class="form-group row padding-field">
          <div class="col-sm-3">
            <strong>{{ T.profileCountry }}</strong>
          </div>
          <div class="col-sm-9 field-data">
            <strong>{{ profile.country }}</strong>
          </div>
        </div>

        <div class="form-group row padding-field">
          <div class="col-sm-3">
            <strong>{{ T.profileState }}</strong>
          </div>
          <div class="col-sm-9 field-data">
            <strong>{{ profile.state }}</strong>
          </div>
        </div>

        <div class="form-group row padding-field">
          <div class="col-sm-3">
            <strong>{{ T.profileSchool }}</strong>
          </div>
          <div class="col-sm-9 field-data">
            <a :href="`/schools/profile/${profile.school_id}/`"
              ><strong>{{ profile.school }}</strong></a
            >
          </div>
        </div>

        <div class="form-group row padding-field">
          <div class="col-sm-3">
            <strong>{{ T.profileGraduationDate }}</strong>
          </div>
          <div class="col-sm-9 field-data">
            <strong>{{ profile.graduation_date }}</strong>
          </div>
        </div>

        <div class="form-group row padding-field">
          <div class="col-sm-3">
            <strong>{{ T.profileAuthorRank }}</strong
            ><a href="https://blog.omegaup.com/categorias/" target="_blank"
              ><em class="glyphicon glyphicon-question-sign"></em
            ></a>
          </div>
          <div class="col-sm-9 field-data">
            <strong>
              {{ profile.rankinfo.author_ranking || T.authorRankUnranked }}
            </strong>
          </div>
        </div>
      </div>
    </div>
    <a v-if="!profile.is_private" :href="`/submissions/${profile.username}/`">
      {{ T.wordsSeeLatestSubmissions }}
    </a>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import user_Username from './Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class UserBasicInfo extends Vue {
  @Prop() profile!: types.UserProfile;
  @Prop() rank!: string;
  T = T;
}
</script>

<style>
.fields-border > .form-group,
.fields-border div > .form-group {
  border-color: #e9e9e9 !important;
  border-style: solid !important;
  border-width: 0 0 0.05rem 0 !important;
}

.field-data {
  color: gray;
}

.padding-field {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
</style>

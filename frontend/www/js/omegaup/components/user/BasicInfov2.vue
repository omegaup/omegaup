<template>
  <div class="card-body">
    <div>
      <ul class="profile-fields fields-border">
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileUsername }}</strong>
            </div>
            <div class="field-data">
              https://omegaup.com/profile/<strong
                ><omegaup-user-username
                  v-bind:classname="profile.classname"
                  v-bind:username="profile.username"
                ></omegaup-user-username></strong
              >/
            </div>
          </div>
        </li>
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profile }}</strong>
            </div>
            <div class="field-data">
              {{ profile.name }}
            </div>
          </div>
        </li>
        <li v-if="profile.email">
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileEmail }}</strong>
            </div>
            <div class="field-data">
              Primary: <strong>{{ profile.email }}</strong
              >&nbsp;
            </div>
          </div>
        </li>
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileCountry }}</strong>
            </div>
            <div class="field-data">
              <strong>{{ profile.country }}</strong>
            </div>
          </div>
        </li>
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileState }}</strong>
            </div>
            <div class="field-data">
              <strong>{{ profile.state }}</strong>
            </div>
          </div>
        </li>
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileSchool }}</strong>
            </div>
            <div class="field-data">
              <a v-bind:href="`/schools/profile/${profile.school_id}/`"
                ><strong>{{ profile.school }}</strong></a
              >
            </div>
          </div>
        </li>
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileGraduationDate }}</strong>
            </div>
            <div class="field-data">
              <strong>{{ profile.graduation_date }}</strong>
            </div>
          </div>
        </li>
        <li>
          <div class="padding-field">
            <div class="field-name">
              <strong>{{ T.profileRank }}</strong
              ><a href="https://blog.omegaup.com/categorias/" target="_blank"
                ><em class="glyphicon glyphicon-question-sign"></em
              ></a>
            </div>
            <div class="field-data">
              <strong
                ><omegaup-user-username
                  v-bind:classname="profile.classname"
                  v-bind:username="rank"
                ></omegaup-user-username
              ></strong>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <a
      v-if="!profile.is_private"
      v-bind:href="`/submissions/${profile.username}/`"
    >
      {{ T.wordsSeeLatestSubmissions }}
    </a>
  </div>
</template>

<style>
.profile-fields .field-name {
  color: #333;
  float: left;
  width: 10rem;
}

.fields-border > li {
  border-color: #e9e9e9;
  border-style: solid;
  border-width: 0 0 0.05rem 0;
}

.field-data {
  color: gray;
}

li:not(first-child) {
  border-width: 0;
}

.padding-field {
  padding-top: 0.5rem;
  padding-bottom: 0.5rem;
}
</style>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import user_Username from './Username.vue';

@Component({
  components: {
    'omegaup-user-username': user_Username,
  },
})
export default class UserBasicInfo extends Vue {
  @Prop() profile!: omegaup.Profile;
  @Prop() rank!: string;
  T = T;
}
</script>

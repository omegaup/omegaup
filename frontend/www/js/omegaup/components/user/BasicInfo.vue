<template>
  <div class="panel-body">
    <div>
      <ul class="uiList fbSettingsList _4kg _6-h _4ks">
        <li class="fbSettingsListItem clearfix uiListItem"></li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileUsername }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              https://omegaup.com/profile/<strong><omegaup-user-username v-bind:classname=
              "profile.classname"
                                     v-bind:username=
                                     "profile.username"></omegaup-user-username></strong>/
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profile }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              {{ profile.name }}
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem"
            v-if="profile.email">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileEmail }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              Primary: <strong>{{ profile.email }}</strong>&nbsp;
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileCountry }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              <strong>{{ profile.country }}</strong>
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileState }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              <strong>{{ profile.state }}</strong>
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileSchool }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              <strong>{{ profile.school }}</strong>
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileGraduationDate }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              <strong>{{ profile.graduation_date }}</strong>
            </div>
          </div>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileRank }}</strong><a href="https://blog.omegaup.com/categorias/"
                   target="_blank"><em class="glyphicon glyphicon-question-sign"></em></a>
            </div>
            <div class="fbSettingsListItemContent fcg">
              <strong><omegaup-user-username v-bind:classname="profile.classname"
                                     v-bind:username="rank"></omegaup-user-username></strong>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import {T} from '../../omegaup.js';
import user_Username from './Username.vue';
export default {
  props: {
    profile: Object,
    rank: String,
  },
  data: function() {
    return { T: T, }
  },
  components: {
    'omegaup-user-username': user_Username,
  }
}
</script>

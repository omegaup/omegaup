<template>
  <div class="panel-body">
    <div>
      <ul class="uiList fbSettingsList _4kg _6-h _4ks">
        <li class="fbSettingsListItem clearfix uiListItem"></li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileUsername }}</strong></span>
          <span class="fbSettingsListItemContent fcg">https://omegaup.com/profile/<strong><omegaup-user-username v-bind:classname="profile.classname"
                                 v-bind:username=
                                 "profile.username"></omegaup-user-username></strong>/</span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profile }}</strong></span> <span class=
          "fbSettingsListItemContent fcg">{{ profile.name }}</span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem"
            v-if="profile.email">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileEmail }}</strong></span> <span class=
          "fbSettingsListItemContent fcg">Primary: <strong>{{ profile.email
          }}</strong>&nbsp;</span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileCountry }}</strong></span> <span class=
          "fbSettingsListItemContent fcg"><strong>{{ profile.country }}</strong></span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileState }}</strong></span> <span class=
          "fbSettingsListItemContent fcg"><strong>{{ profile.state }}</strong></span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileSchool }}</strong></span> <span class=
          "fbSettingsListItemContent fcg"><strong>{{ profile.school }}</strong></span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileGraduationDate }}</strong></span>
          <span class="fbSettingsListItemContent fcg"><strong>{{ profile.graduation_date
          }}</strong></span></a>
        </li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <a class="pvm phs fbSettingsListLink clearfix"><span class=
          "pls fbSettingsListItemLabel"><strong>{{ T.profileRank }}</strong></span> <span class=
          "fbSettingsListItemContent fcg"><strong><omegaup-user-username v-bind:classname=
          "profile.classname"
                                 v-bind:username=
                                 "rank"></omegaup-user-username></strong></span></a>
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

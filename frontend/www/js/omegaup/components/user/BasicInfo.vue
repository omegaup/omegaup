<template>
<div class="panel-body">
  <div id="SettingsPage_Content">
    <ul class="uiList fbSettingsList _4kg _6-h _4ks ">
      <li class="fbSettingsListItem clearfix uiListItem"></li>
      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileUsername }}</strong></span>
          <span class="fbSettingsListItemContent fcg">https://omegaup.com/profile/<strong id="username-link">
            <omegaup-user-rankcolor v-bind:username="username" v-bind:classname="classname"></omegaup-user-rankcolor></strong>/</span>
        </a>
      </li>
      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profile }}</strong></span>
          <span class="fbSettingsListItemContent fcg">{{ name }}</span>
        </a>
      </li>
      <li v-if="email" id="user-email-wrapper" class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix">
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileEmail }}</strong></span>
          <span class="fbSettingsListItemContent fcg">Primary: <strong id="user-email"> {{ email }}</strong>&nbsp;</span>
        </a>
      </li>
      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileCountry }}</strong></span>
          <span class="fbSettingsListItemContent fcg"><strong id="user-country">{{ country }}</strong></span></a>
      </li>

      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileState }}</strong></span>
          <span class="fbSettingsListItemContent fcg"><strong id="user-state">{{ state }}</strong></span>
        </a>
      </li>

      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileSchool }}</strong></span>
          <span class="fbSettingsListItemContent fcg"><strong id="user-school">{{ school }}</strong></span>
        </a>
      </li>

      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileGraduationDate }}</strong></span>
          <span class="fbSettingsListItemContent fcg"><strong id="user-graduation-date">{{ graduation_date }}</strong></span>
        </a>
      </li>

      <li class="fbSettingsListItem clearfix uiListItem">
        <a class="pvm phs fbSettingsListLink clearfix" >
          <span class="pls fbSettingsListItemLabel"><strong>{{ T.profileRank }}</strong></span>
          <span class="fbSettingsListItemContent fcg"><strong id="user-rank"><omegaup-user-rankcolor v-bind:username="rank" v-bind:classname="classname"></omegaup-user-rankcolor></strong></span>
        </a>
      </li>
    </ul>
  </div>
</div>
</template>

<script>
import {T} from '../../omegaup.js';
import user_RankColor from './RankColor.vue';
export default {
  props: {
    name: String,
    username: String,
    classname: String,
    email: String,
    country: String,
    state: String,
    school: String,
    graduation_date: String,
    rank: String,
  },
  data: function() {
		return {
			T: T,
		}
  },
  components: {
    'omegaup-user-rankcolor': user_RankColor,
  }
}
</script>

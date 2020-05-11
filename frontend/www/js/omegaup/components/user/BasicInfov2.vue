<template>
  <div class="card-body">
    <div>
      <ul class="uiList fbSettingsList _4kg _6-h _4ks">
        <li class="fbSettingsListItem clearfix uiListItem"></li>
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileUsername }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              https://omegaup.com/profile/<strong
                ><omegaup-user-username
                  v-bind:classname="profile.classname"
                  v-bind:username="profile.username"
                ></omegaup-user-username></strong
              >/
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
        <li class="fbSettingsListItem clearfix uiListItem" v-if="profile.email">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileEmail }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              Primary: <strong>{{ profile.email }}</strong
              >&nbsp;
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
              <a v-bind:href="`/schools/profile/${profile.school_id}/`"
                ><strong>{{ profile.school }}</strong></a
              >
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
              <strong>{{ T.profileRank }}</strong
              ><a href="https://blog.omegaup.com/categorias/" target="_blank"
                ><em class="glyphicon glyphicon-question-sign"></em
              ></a>
            </div>
            <div class="fbSettingsListItemContent fcg">
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
.fbSettingsList .fbSettingsListItemLabel {
  color: #333;
  float: left;
  width: 165px;
}

.fbSettingsList .fbSettingsListItemSaved,
.fbSettingsList .fbSettingsListItemEdit {
  color: #3b5998;
  float: right;
  padding-left: 25px;
  padding-right: 5px;
  text-align: right;
}

._4ks > li {
  border-color: #e9e9e9;
  border-style: solid;
  border-width: 0 0 1px 0;
}

.uiListItem {
  display: block;
}

.fcg {
  color: gray;
}

.clearfix {
  zoom: 1;
}

.uiIconText {
  display: inline-block;
  padding-left: 21px;
  position: relative;
}

.fbSettingsList .fbSettingsListItemEdit {
  color: #3b5998;
  float: right;
  padding-left: 25px;
  padding-right: 5px;
  text-align: right;
}

#facebook .hidden_elem {
  display: none !important;
}

.fbSettingsList .fbSettingsListItemSaved {
  color: #777;
}

.clearfix::after {
  clear: both;
  content: '.';
  display: block;
  font-size: 0;
  height: 0;
  line-height: 0;
  visibility: hidden;
}
._4kg._6-h > li:first-child {
  padding-top: 0;
}

.uiListItem:first-child {
  border-width: 0;
}
.fbSettingsList a.fbSettingsListLink {
  background: transparent;
  display: block;
  text-decoration: none;
}

.pvm {
  padding-top: 10px;
  padding-bottom: 10px;
}
.phs {
  padding-left: 5px;
  padding-right: 5px;
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

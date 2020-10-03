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
        <li v-if="profile.email" class="fbSettingsListItem clearfix uiListItem">
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
        <li class="fbSettingsListItem clearfix uiListItem">
          <div class="pvm phs fbSettingsListLink clearfix">
            <div class="pls fbSettingsListItemLabel">
              <strong>{{ T.profileAuthorRank }}</strong>
            </div>
            <div class="fbSettingsListItemContent fcg">
              <strong>
                {{ profile.rankinfo.author_ranking || T.authorRankUnranked }}
              </strong>
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

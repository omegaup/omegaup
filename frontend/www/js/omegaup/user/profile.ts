import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import mainStore from '../mainStore';

import user_Profile from '../components/user/Profile.vue';
import { ViewProfileTabs } from '../components/user/ViewProfile.vue';

// Define minimum interfaces needed for heatmap data
interface HeatmapDataPoint {
  date: string;
  count: number;
}

// Use type declaration merging to add the methods to Vue
declare module 'vue/types/vue' {
  interface Vue {
    loadInitialHeatmapData(username: string): void;
    loadHeatmapDataForYear(username: string, year: number): void;
  }
}

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHash = window.location.hash.substring(1).split('#');
  const searchResultSchools: types.SchoolListItem[] = [];
  const currentYear = new Date().getFullYear();

  if (payload.profile.school && payload.profile.school_id) {
    searchResultSchools.push({
      key: payload.profile.school_id,
      value: payload.profile.school,
    });
  }

  let selectedTab = locationHash[0] || 'view-profile';
  let viewProfileSelectedTab: string | null = null;
  if (selectedTab === 'locale-changed') {
    selectedTab = 'edit-preferences';
    history.replaceState({}, 'updateTab', `#${selectedTab}`);
    ui.success(T.userEditPreferencesSuccess);
  } else {
    for (const viewProfileTab of Object.values(ViewProfileTabs)) {
      if (selectedTab === viewProfileTab) {
        viewProfileSelectedTab = viewProfileTab;
        selectedTab = 'view-profile';
        break;
      }
    }
  }

  const userProfile = new Vue({
    el: '#main-container',
    components: {
      'omegaup-user-profile': user_Profile,
    },
    data: () => {
      return {
        profile: payload.profile,
        data: payload.extraProfileDetails,
        identities: payload.identities,
        apiTokens: commonPayload.apiTokens,
        hasPassword: payload.extraProfileDetails?.hasPassword,
        selectedTab,
        searchResultSchools: searchResultSchools,
        heatmapData: [] as HeatmapDataPoint[],
        availableYears: [currentYear] as number[],
        isLoading: true,
      };
    },
    mounted: function () {
      if (this.profile.username) {
        this.loadInitialHeatmapData(this.profile.username);
      }
    },
    methods: {
      loadInitialHeatmapData: function (username: string): void {
        this.isLoading = true;

        api.User.stats({ username })
          .then((response) => {
            const processedData: HeatmapDataPoint[] = [];
            if (response.runs && response.runs.length > 0) {
              for (const run of response.runs) {
                if (run.date) {
                  processedData.push({
                    date: run.date,
                    count: run.runs,
                  });
                }
              }
            }

            this.heatmapData = processedData;

            if (response.runs && response.runs.length > 0) {
              const sortedRuns = [...response.runs].sort((a, b) => {
                if (!a.date) return 1;
                if (!b.date) return -1;
                return new Date(a.date).getTime() - new Date(b.date).getTime();
              });

              if (sortedRuns[0].date) {
                const firstSubmissionYear = new Date(
                  sortedRuns[0].date,
                ).getFullYear();

                const years: number[] = [];
                const currentYear = new Date().getFullYear();

                for (
                  let year = firstSubmissionYear;
                  year <= currentYear;
                  year++
                ) {
                  years.push(year);
                }

                years.sort((a, b) => b - a);
                this.availableYears = years;
              }
            }

            this.isLoading = false;
          })
          .catch((error) => {
            ui.apiError(error);
            this.isLoading = false;
          });
      },
      loadHeatmapDataForYear: function (username: string, year: number): void {
        this.isLoading = true;

        api.User.stats({ username, year: year.toString() })
          .then((response) => {
            const processedData: HeatmapDataPoint[] = [];
            if (response.runs && response.runs.length > 0) {
              for (const run of response.runs) {
                if (run.date) {
                  processedData.push({
                    date: run.date,
                    count: run.runs,
                  });
                }
              }
            }

            this.heatmapData = processedData;

            this.isLoading = false;
          })
          .catch((error) => {
            ui.apiError(error);
            this.isLoading = false;
          });
      },
    },
    render: function (createElement) {
      return createElement('omegaup-user-profile', {
        props: {
          data: payload.extraProfileDetails,
          profile: this.profile,
          profileBadges: new Set(
            payload.extraProfileDetails?.ownedBadges?.map(
              (badge) => badge.badge_alias,
            ),
          ),
          visitorBadges: new Set(payload.extraProfileDetails?.badges),
          selectedTab: this.selectedTab,
          identities: this.identities,
          apiTokens: this.apiTokens,
          countries: payload.countries,
          programmingLanguages: payload.programmingLanguages,
          hasPassword: this.hasPassword,
          viewProfileSelectedTab,
          searchResultSchools: this.searchResultSchools,
          heatmapData: this.heatmapData,
          availableYears: this.availableYears,
          isLoading: this.isLoading,
        },
        on: {
          'update-user-basic-information': (
            userBasicInformation: Partial<types.UserProfileInfo>,
          ) => {
            api.User.update(userBasicInformation)
              .then(() => {
                mainStore.commit(
                  'updateUsername',
                  userBasicInformation.username,
                );

                userProfile.profile.username = userBasicInformation.username;
                userProfile.profile.country_id =
                  userBasicInformation.country_id;

                ui.success(T.userEditSuccess);
              })
              .catch(ui.apiError);
          },
          'update-user-basic-information-error': ({
            description,
          }: {
            description: string;
          }) => {
            ui.error(description);
          },
          'update-user-preferences': ({
            userPreferences,
            localeChanged,
          }: {
            userPreferences: Partial<types.UserProfileInfo>;
            localeChanged: boolean;
          }) => {
            const profile = {
              ...userPreferences,
              ...{ username: this.profile.username },
            };
            api.User.update(profile)
              .then(() => {
                if (localeChanged) {
                  window.location.hash = 'locale-changed';
                  window.location.reload();
                  return;
                }
                ui.success(T.userEditPreferencesSuccess);
              })
              .catch(ui.apiError);
          },
          'update-user-schools': (
            schoolInformation: Partial<types.UserProfileInfo>,
          ) => {
            api.User.update(schoolInformation)
              .then(() => {
                ui.success(T.userEditSchoolSuccess);
              })
              .catch(ui.apiError);
          },
          'add-identity': ({
            username,
            password,
          }: {
            username: string;
            password: string;
          }) => {
            api.User.associateIdentity({
              username: username,
              password: password,
            })
              .then(() => {
                refreshIdentityList();
                ui.success(T.profileIdentityAdded);
              })
              .catch(ui.apiError);
          },
          'update-password': ({
            oldPassword,
            newPassword,
          }: {
            oldPassword: string;
            newPassword: string;
          }) => {
            api.User.changePassword({
              old_password: oldPassword,
              password: newPassword,
            })
              .then(() => {
                ui.success(T.passwordResetResetSuccess);
              })
              .catch(ui.apiError);
          },
          'add-password': ({
            username,
            password,
          }: {
            username: string;
            password: string;
          }) => {
            api.User.updateBasicInfo({
              username,
              password,
            })
              .then(() => {
                ui.success(T.passwordAddRequestSuccess);
                userProfile.hasPassword = true;
                userProfile.selectedTab = 'change-password';
              })
              .catch(ui.apiError);
          },
          'update-search-result-schools': (query: string) => {
            api.School.list({ query })
              .then(({ results }) => {
                if (!results.length) {
                  this.searchResultSchools = [
                    {
                      key: 0,
                      value: query,
                    },
                  ];
                  return;
                }
                this.searchResultSchools = results.map(
                  ({ key, value }: types.SchoolListItem) => ({
                    key,
                    value,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'request-delete-account': () => {
            api.User.deleteRequest()
              .then(({ token }) => {
                api.User.deleteConfirm({ token })
                  .then(() => {
                    // Log out the user
                    window.location.href = '/logout/';
                  })
                  .catch(ui.apiError);
              })
              .catch(ui.apiError);
          },
          'create-api-token': (tokenName: string) => {
            api.User.createAPIToken({ name: tokenName })
              .then(({ token }) => {
                refreshApiTokensList();
                ui.success(
                  ui.formatString(T.apiTokenSuccessfullyCreated, {
                    token: token,
                  }),
                  false,
                );
              })
              .catch(ui.apiError);
          },
          'revoke-api-token': (tokenName: string) => {
            api.User.revokeAPIToken({ name: tokenName })
              .then(() => {
                refreshApiTokensList();
                ui.success(T.apiTokenSuccessfullyRevoked);
              })
              .catch(ui.apiError);
          },
          'heatmap-year-changed': (year: number) => {
            if (!this.profile.username) return;
            this.loadHeatmapDataForYear(this.profile.username, year);
          },
        },
      });
    },
  });

  function refreshIdentityList() {
    api.User.listAssociatedIdentities({})
      .then((data) => {
        userProfile.identities = data.identities;
      })
      .catch(ui.apiError);
  }
  function refreshApiTokensList() {
    api.User.listAPITokens({})
      .then((data) => {
        userProfile.apiTokens = data.tokens;
      })
      .catch(ui.apiError);
  }
});

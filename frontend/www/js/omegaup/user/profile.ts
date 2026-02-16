import * as Highcharts from 'highcharts/highstock';
import Vue from 'vue';
import * as api from '../api';
import { types } from '../api_types';
import T from '../lang';
import mainStore from '../mainStore';
import { OmegaUp } from '../omegaup';
import * as ui from '../ui';

import user_Profile, {
  ProfileStatistics,
} from '../components/user/Profile.vue';
import { ViewProfileTabs } from '../components/user/ViewProfile.vue';

// Heatmap utility interfaces and functions
export interface HeatmapColors {
  emptyCell: string;
  lowActivity: string;
  mediumActivity: string;
  highActivity: string;
  background: string;
  wrapper: string;
}

export interface HeatmapChartParams {
  heatmapContainer: HTMLElement;
  formattedData: Array<[number, number, number]>;
  startDate: Date;
  firstDayOffset: number;
  totalWeeks: number;
  boxWidth: number;
  boxHeight: number;
  boxPadding: number;
  chartWidth: number;
  chartHeight: number;
  colors: HeatmapColors;
}

/**
 * Get Highcharts options for the user activity heatmap
 * @param params - Parameters for chart configuration
 * @returns Highcharts.Options object
 */
export function getHeatmapChartOptions(
  params: HeatmapChartParams,
): Highcharts.Options {
  const {
    heatmapContainer,
    formattedData,
    startDate,
    firstDayOffset,
    totalWeeks,
    boxWidth,
    boxHeight,
    boxPadding,
    chartWidth,
    chartHeight,
    colors,
  } = params;

  return {
    chart: {
      renderTo: heatmapContainer,
      type: 'heatmap',
      height: chartHeight,
      width: chartWidth,
      spacing: [0, 0, 10, 0],
      margin: [0, 0, 15, 0],
      backgroundColor: colors.background,
    },
    title: {
      text: '',
    },
    subtitle: {
      text: '',
    },
    xAxis: {
      min: 0,
      max: totalWeeks,
      labels: {
        enabled: true,
        formatter: function () {
          const weekFirstDay = new Date(startDate);
          weekFirstDay.setDate(
            weekFirstDay.getDate() + (this.value as number) * 7,
          );

          if (weekFirstDay.getDate() <= 7) {
            return weekFirstDay.toLocaleString('default', {
              month: 'short',
            });
          }
          return '';
        },
        style: {
          fontSize: '9px',
          fontWeight: 'bold',
        },
        y: 7,
        align: 'left',
      },
      lineWidth: 0,
      tickWidth: 0,
      tickPositioner: function () {
        const positions = [];
        for (let month = 0; month < 12; month++) {
          const monthFirstDay = new Date(startDate.getFullYear(), month, 1);
          const dayOfYear = Math.floor(
            (monthFirstDay.getTime() - startDate.getTime()) /
              (24 * 60 * 60 * 1000),
          );
          const weekNumber = Math.floor((dayOfYear + firstDayOffset) / 7);
          positions.push(weekNumber);
        }
        return positions;
      },
    },
    yAxis: {
      min: 0,
      max: 6,
      labels: {
        enabled: false,
      },
      lineWidth: 0,
      tickWidth: 0,
    },
    colorAxis: {
      dataClasses: [
        { from: -1, to: 0, color: colors.emptyCell },
        { from: 1, to: 4, color: colors.lowActivity },
        { from: 5, to: 9, color: colors.mediumActivity },
        { from: 10, to: 1000, color: colors.highActivity },
      ],
      labels: {
        enabled: false,
      },
    },
    tooltip: {
      formatter: function () {
        const date = new Date(startDate);
        const dayOffset =
          (this.point.x as number) * 7 +
          (this.point.y as number) -
          firstDayOffset;
        date.setDate(date.getDate() + dayOffset);

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const formattedDate = `${year}-${month}-${day}`;

        const value = this.point.value as number;
        if (value > 0) {
          return `<b>${formattedDate}</b><br>Total submissions: <b>${value}</b>`;
        }
        return `<b>${formattedDate}</b>`;
      },
    },
    legend: {
      enabled: false,
    },
    credits: {
      enabled: false,
    },
    series: [
      {
        name: 'Submissions',
        borderWidth: 0.1,
        borderColor: '#ffffff',
        data: formattedData,
        dataLabels: {
          enabled: false,
        },
        type: 'heatmap' as const,
        pointWidth: boxWidth,
        pointHeight: boxHeight,
        pointPadding: boxPadding / 2,
        states: {
          hover: {
            brightness: 0.1,
            borderColor: '#ffffff',
          },
        },
      } as Highcharts.SeriesHeatmapOptions,
    ],
  };
}

// Define minimum interfaces needed for heatmap data
export interface HeatmapDataPoint {
  date: string;
  count: number;
}

// Use type declaration merging to add the methods to Vue
declare module 'vue/types/vue' {
  interface Vue {
    loadInitialData(username: string): void;
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
        availableYears: [currentYear] as number[],
        isLoading: true,
        profileStatistics: null as ProfileStatistics | null,
      };
    },
    mounted: function () {
      if (this.profile.username) {
        this.loadInitialData(this.profile.username);
      }
    },
    methods: {
      /**
       * Loads all profile data including stats for both charts
       */
      loadInitialData: function (username: string): void {
        this.isLoading = true;

        api.User.stats({ username })
          .then((response) => {
            // Store runs data directly
            if (this.data) {
              if (!this.data.stats) {
                this.data.stats = [];
              }
              this.data.stats = response.runs || [];
            }

            // Find all available years from runs data
            const years = new Set<number>();
            const currentYear = new Date().getFullYear();
            years.add(currentYear);

            for (const run of response.runs || []) {
              if (run.date) {
                const year = parseInt(run.date.split('-')[0], 10);
                if (!isNaN(year)) {
                  years.add(year);
                }
              }
            }

            this.availableYears = [...years].sort((a, b) => b - a);
            this.isLoading = false;
          })
          .catch((error) => {
            ui.apiError(error);
            this.isLoading = false;
          });

        // Also fetch profile statistics for the charts
        api.User.profileStatistics({ username })
          .then((response) => {
            this.profileStatistics = response;
          })
          .catch((error) => {
            // Non-blocking error - just log it
            console.error('Failed to load profile statistics:', error);
          });
      },

      /**
       * Loads data for a specific year for both charts
       */
      loadHeatmapDataForYear: function (username: string, year: number): void {
        this.isLoading = true;

        api.User.stats({ username, year: year.toString() })
          .then((response) => {
            // Store runs data directly
            if (this.data) {
              if (!this.data.stats) {
                this.data.stats = [];
              }
              this.data.stats = response.runs || [];
            }

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
          availableYears: this.availableYears,
          isLoading: this.isLoading,
          profileStatistics: this.profileStatistics,
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

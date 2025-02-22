import Vue from 'vue';
import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import * as api from '../api';
import * as ui from '../ui';
import T from '../lang';
import mainStore from '../mainStore';

import user_Profile from '../components/user/Profile.vue';
import { ViewProfileTabs } from '../components/user/ViewProfile.vue';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.UserProfileDetailsPayload();
  const commonPayload = types.payloadParsers.CommonPayload();
  const locationHash = window.location.hash.substring(1).split('#');
  const searchResultSchools: types.SchoolListItem[] = [];

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
        files: [] as string[],
        isAdmin: payload.isAdmin,
      };
    },
    mounted() {
      if (this.selectedTab === 'manage-files') {
        this.fetchFiles();
      }
    },
    methods: {
      fetchFiles,
      addFile,
      deleteFile,
      downloadFile,
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
          files: this.files,
          isAdmin: this.isAdmin,
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
          'fetch-files': () => {
            this.fetchFiles();
          },
          'add-file': (file: File) => {
            this.addFile(file);
          },
          'delete-file': (fileId: string) => {
            this.deleteFile(fileId);
          },
          'download-file': (fileName: string) => {
            this.downloadFile(fileName);
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
  function fetchFiles() {
    api.Admin.listFiles()
      .then(({ files }) => {
        userProfile.files = files;
      })
      .catch(ui.apiError);
  }
  function fileToBase64(file: File): Promise<string> {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = () => resolve(reader.result as string);
      reader.onerror = (error) => reject(error);
    });
  }

  async function addFile(file: File) {
    try {
      const fileBase64 = await fileToBase64(file);

      api.Admin.uploadFile({ file: fileBase64, filename: file.name }) // Include original filename
        .then(() => {
          ui.success(T.fileUploadSuccess);
          fetchFiles();
        })
        .catch(ui.apiError);
    } catch (error: any) {
      console.error('File conversion failed', error);
      ui.apiError(error);
    }
  }

  function deleteFile(filename: string) {
    api.Admin.deleteFile({ filename })
      .then(() => {
        ui.success(T.fileDeleteSuccess);
        fetchFiles();
      })
      .catch(ui.apiError);
  }

  function downloadFile(filename: string) {
    const downloadUrl = `/api/admin/downloadFile?filename=${encodeURIComponent(
      filename,
    )}`;

    fetch(downloadUrl, { method: 'GET' })
      .then((response) => {
        if (!response.ok) {
          throw new Error('Failed to download file');
        }
        return response.blob();
      })
      .then((blob) => {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', filename); // Set correct filename
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
      })
      .catch(ui.apiError);
  }
});

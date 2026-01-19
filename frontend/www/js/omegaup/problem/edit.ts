import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_Edit from '../components/problem/Edit.vue';
import * as ui from '../ui';
import * as api from '../api';
import store from './creator/store';

import { BootstrapVue, BootstrapVueIcons } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

OmegaUp.on('ready', () => {
  Vue.use(BootstrapVue);
  Vue.use(BootstrapVueIcons);
  const payload = types.payloadParsers.ProblemEditPayload();
  if (payload.statusError) {
    ui.error(payload.statusError);
  }
  if (payload.statusSuccess) {
    ui.success(T.problemEditUpdatedSuccessfully);
  }
  const solutions: types.Statements = {
    [payload.statement?.language || 'es']: payload.solution?.markdown || '',
  };
  const problemEdit = new Vue({
    el: '#main-container',
    store,
    components: {
      'omegaup-problem-edit': problem_Edit,
    },
    data: () => ({
      admins: payload.admins,
      groups: payload.groupAdmins,
      initialTab: window.location.hash
        ? window.location.hash.substring(1)
        : 'edit',
      publishedRevision: payload.publishedRevision,
      statement: payload.statement,
      solution: payload.solution || {
        language: 'es',
        markdown: '',
        images: {},
      },
      cdp: payload.cdp || null,
      problemLevel: payload.problemLevel,
      selectedPublicTags: payload.selectedPublicTags,
      selectedPrivateTags: payload.selectedPrivateTags,
      searchResultUsers: [] as types.ListItem[],
      searchResultGroups: [] as types.ListItem[],
    }),
    methods: {
      refreshProblemAdmins: (): void => {
        api.Problem.admins({ problem_alias: payload.alias })
          .then((data) => {
            problemEdit.admins = data.admins;
            problemEdit.groups = data.group_admins;
          })
          .catch(ui.apiError);
      },
    },
    render: function (createElement) {
      return createElement('omegaup-problem-edit', {
        props: {
          data: payload,
          initialTab: this.initialTab,
          originalVisibility: payload.visibility,
          admins: this.admins,
          groups: this.groups,
          log: payload.log,
          publishedRevision: this.publishedRevision,
          value: this.publishedRevision,
          statement: this.statement,
          solution: this.solution,
          cdp: this.cdp,
          problemLevel: this.problemLevel,
          selectedPublicTags: this.selectedPublicTags,
          selectedPrivateTags: this.selectedPrivateTags,
          searchResultUsers: this.searchResultUsers,
          searchResultGroups: this.searchResultGroups,
        },
        on: {
          'update-problem-level': (levelTag?: string) => {
            api.Problem.updateProblemLevel({
              problem_alias: payload.alias,
              level_tag: levelTag,
            })
              .then(() => {
                ui.success(T.problemLevelUpdated);
                this.problemLevel = levelTag;
              })
              .catch(ui.apiError);
          },
          'update-markdown-contents': (
            statements: types.Statements,
            language: string,
            currentMarkdown: string,
            markdownType: string,
          ) => {
            // First update markdown contents to current markdown, otherwise
            // component won't detect any change if two different language
            // solutions are the same.
            if (markdownType === 'statements') {
              problemEdit.statement.markdown = currentMarkdown;
              if (Object.prototype.hasOwnProperty.call(statements, language)) {
                problemEdit.statement = {
                  language: language,
                  markdown: statements[language],
                  images: {},
                  sources: {},
                };
                return;
              }
              api.Problem.details(
                {
                  problem_alias: payload.alias,
                  statement_type: 'markdown',
                  show_solvers: false,
                  lang: language,
                },
                { quiet: true },
              )
                .then((response) => {
                  if (response.statement.language !== language) {
                    response.statement.markdown = '';
                  }
                  statements[language] = response.statement.markdown;
                  problemEdit.statement = response.statement;
                })
                .catch((error) => {
                  if (error.httpStatusCode == 404) {
                    return;
                  }
                  ui.apiError(error);
                });
            } else {
              problemEdit.solution.markdown = currentMarkdown;
              if (Object.prototype.hasOwnProperty.call(solutions, language)) {
                problemEdit.solution.markdown = solutions[language];
                return;
              }
              api.Problem.solution(
                {
                  problem_alias: payload.alias,
                  lang: language,
                },
                { quiet: true },
              )
                .then((response) => {
                  const solution = response.solution || {
                    language: 'es',
                    markdown: '',
                    images: {},
                  };
                  if (solution.language !== language) {
                    solution.markdown = '';
                  }
                  solutions[language] = solution.markdown;
                  problemEdit.solution = solution;
                })
                .catch((error) => {
                  if (error.httpStatusCode == 404) {
                    return;
                  }
                  ui.apiError(error);
                });
            }
          },
          'add-tag': (alias: string, tagname: string, isPublic: boolean) => {
            api.Problem.addTag({
              problem_alias: alias,
              name: tagname,
              public: isPublic,
            })
              .then(() => {
                ui.success(T.tagAdded);
                if (isPublic) {
                  this.selectedPublicTags.push(tagname);
                } else {
                  this.selectedPrivateTags.push(tagname);
                }
              })
              .catch(ui.apiError);
          },
          'remove-tag': (alias: string, tagname: string, isPublic: boolean) => {
            api.Problem.removeTag({
              problem_alias: alias,
              name: tagname,
            })
              .then(() => {
                ui.success(T.tagRemoved);
                // FIXME: For some reason this is not being reactive
                if (isPublic) {
                  this.selectedPublicTags = this.selectedPublicTags.filter(
                    (tag) => tag !== tagname,
                  );
                } else {
                  this.selectedPrivateTags = this.selectedPrivateTags.filter(
                    (tag) => tag !== tagname,
                  );
                }
              })
              .catch(ui.apiError);
          },
          'change-allow-user-add-tag': (
            alias: string,
            title: string,
            allowTags: boolean,
          ) => {
            api.Problem.update({
              problem_alias: alias,
              title: title,
              allow_user_add_tags: allowTags,
              message: `${T.problemEditFormAllowUserAddTags}: ${allowTags}`,
            })
              .then(() => {
                ui.success(T.problemEditUpdatedSuccessfully);
              })
              .catch(ui.apiError);
          },
          'add-admin': (username: string): void => {
            api.Problem.addAdmin({
              problem_alias: payload.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminAdded);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-admin': (username: string): void => {
            api.Problem.removeAdmin({
              problem_alias: payload.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminRemoved);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
          'add-group-admin': (groupAlias: string): void => {
            api.Problem.addGroupAdmin({
              problem_alias: payload.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminAdded);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-group-admin': (groupAlias: string): void => {
            api.Problem.removeGroupAdmin({
              problem_alias: payload.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminRemoved);
                this.refreshProblemAdmins();
              })
              .catch(ui.apiError);
          },
          'select-version': (
            selectedRevision: types.ProblemVersion,
            updatePublished: boolean,
          ) => {
            api.Problem.selectVersion({
              problem_alias: payload.alias,
              commit: selectedRevision.commit,
              update_published: updatePublished,
            })
              .then(() => {
                problemEdit.publishedRevision = selectedRevision;
                ui.success(T.problemVersionUpdated);
              })
              .catch(ui.apiError);
          },
          'runs-diff': (
            versions: types.CommitRunsDiff,
            selectedCommit: types.ProblemVersion,
          ) => {
            api.Problem.runsDiff({
              problem_alias: payload.alias,
              version: selectedCommit.version,
            })
              .then((response) => {
                Vue.set(
                  versions.runsDiff,
                  selectedCommit.version,
                  response.diff,
                );
              })
              .catch(ui.apiError);
          },
          remove: (problemAlias: string) => {
            api.Problem.delete({ problem_alias: problemAlias })
              .then(() => {
                window.location.href = '/problem/mine/';
              })
              .catch(ui.apiError);
          },
          'update-search-result-users': (query: string) => {
            api.User.list({ query, rowcount: 10 })
              .then(({ results }) => {
                this.searchResultUsers = results.map(
                  ({ key, value }: types.ListItem, index) => ({
                    key,
                    value: `${String(index + 1).padStart(2, '0')}.- ${ui.escape(
                      value,
                    )} (<strong>${ui.escape(key)}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'update-search-result-groups': (query: string) => {
            api.Group.list({
              query,
            })
              .then((data) => {
                // Groups previously added into the contest should not be
                // shown in the dropdown
                const addedGroups = new Set(
                  this.groups.map((group) => group.alias),
                );
                this.searchResultGroups = data
                  .filter((group) => !addedGroups.has(group.value))
                  .map((group) => ({
                    key: group.value,
                    value: `${ui.escape(group.label)} (<strong>${ui.escape(
                      group.value,
                    )}</strong>)`,
                  }));
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});

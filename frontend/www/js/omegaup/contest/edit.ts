import { OmegaUp } from '../omegaup';
import { types } from '../api_types';
import Vue from 'vue';
import T from '../lang';
import contest_Edit from '../components/contest/Edit.vue';
import SearchTypes from '../components/contest/AddProblem.vue';
import * as ui from '../ui';
import * as api from '../api';
import { toCsv, TableCell } from '../csv';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ContestEditPayload();
  const searchResultTeamsGroups: types.ListItem[] = [];
  if (payload.teams_group) {
    searchResultTeamsGroups.push({
      key: payload.teams_group.alias,
      value: payload.teams_group.name,
    });
  }
  const contestEdit = new Vue({
    el: '#main-container',
    components: {
      'omegaup-contest-edit': contest_Edit,
    },
    data: () => ({
      admins: payload.admins,
      details: payload.details,
      initialTab: window.location.hash?.substring(1) || '',
      groupAdmins: payload.group_admins,
      groups: payload.groups,
      problems: payload.problems,
      requests: payload.requests,
      users: payload.users,
      searchResultProblems: [] as types.ListItem[],
      searchResultUsers: [] as types.ListItem[],
      searchResultTeamsGroups,
      searchResultGroups: [] as types.ListItem[],
      teamsGroup: payload.teams_group,
      certificatesDetails: payload.certificatesDetails,
      invalidParameterName: null as null | string,
    }),
    methods: {
      arbitrateRequest: (
        username: string,
        resolution: boolean,
        resolutionText: null | string = null,
      ): void => {
        if (!resolutionText) {
          resolutionText = resolution ? T.wordAccepted : T.wordsDenied;
        }
        api.Contest.arbitrateRequest({
          contest_alias: payload.details.alias,
          username,
          resolution,
          note: resolutionText,
        })
          .then(() => {
            if (resolution) {
              ui.success(T.arbitrateRequestAcceptSuccessfully);
            } else {
              ui.success(T.arbitrateRequestDenySuccessfully);
            }
            contestEdit.refreshRequests();
          })
          .catch(ui.apiError);
      },
      refreshDetails: (): void => {
        api.Contest.adminDetails({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.details = response;
          })
          .catch(ui.apiError);
      },
      refreshGroups: (): void => {
        api.Contest.users({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.groups = response.groups;
          })
          .catch(ui.apiError);
      },
      refreshProblems: (problemAdded: boolean): void => {
        api.Contest.problems({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.problems = response.problems;
            if (
              problemAdded &&
              !contestEdit.details.languages.includes('cat') &&
              contestEdit.problems.some((problem) =>
                problem.languages.split(',').includes('cat'),
              )
            ) {
              api.Contest.update({
                contest_alias: contestEdit.details.alias,
                languages: contestEdit.details.languages.concat(['cat']),
              })
                .then(() => {
                  contestEdit.details.languages.push('cat');
                  ui.warning(T.contestEditCatLanguageAddedWarning);
                })
                .catch(ui.apiError);
            }
          })
          .catch(ui.apiError);
      },
      refreshRequests: (): void => {
        api.Contest.requests({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.requests = response.users;
          })
          .catch(ui.apiError);
      },
      refreshUsers: (): void => {
        api.Contest.users({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.users = response.users;
          })
          .catch(ui.apiError);
      },
      refreshAdmins: (): void => {
        api.Contest.admins({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.admins = response.admins;
          })
          .catch(ui.apiError);
      },
      refreshGroupAdmins: (): void => {
        api.Contest.admins({
          contest_alias: payload.details.alias,
        })
          .then((response) => {
            contestEdit.groupAdmins = response.group_admins;
          })
          .catch(ui.apiError);
      },
      downloadCsvFile: (fileName: string, table: TableCell[][]): void => {
        const blob = new Blob([toCsv(table)], {
          type: 'text/csv;charset=utf-8;',
        });
        const hiddenElement = document.createElement('a');
        hiddenElement.href = window.URL.createObjectURL(blob);
        hiddenElement.target = '_blank';
        hiddenElement.download = fileName;
        hiddenElement.click();
      },
    },
    render: function (createElement) {
      return createElement('omegaup-contest-edit', {
        props: {
          admins: this.admins,
          details: this.details,
          initialTab: this.initialTab,
          groupAdmins: this.groupAdmins,
          groups: this.groups,
          problems: this.problems,
          requests: this.requests,
          users: this.users,
          searchResultProblems: this.searchResultProblems,
          searchResultUsers: this.searchResultUsers,
          searchResultTeamsGroups: this.searchResultTeamsGroups,
          searchResultGroups: this.searchResultGroups,
          teamsGroup: this.teamsGroup,
          originalContestAdmissionMode: payload.original_contest_admission_mode,
          certificatesDetails: this.certificatesDetails,
          invalidParameterName: this.invalidParameterName,
        },
        on: {
          'update-search-result-problems': ({
            query,
            searchType,
          }: {
            query: string;
            searchType: SearchTypes;
          }) => {
            api.Problem.listForTypeahead({
              query,
              search_type: searchType,
            })
              .then((data) => {
                // Problems previously added into the contest should not be
                // shown in the dropdown
                const addedProblems = new Set(
                  this.problems.map((problem) => problem.alias),
                );
                this.searchResultProblems = data.results
                  .filter((problem) => !addedProblems.has(problem.key))
                  .map(({ key, value }, index) => ({
                    key: key,
                    value: `${String(index + 1).padStart(
                      2,
                      '0',
                    )}.-  ${ui.escape(value)} (<strong>${ui.escape(
                      key,
                    )}</strong>)`,
                  }));
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
          'update-search-result-users': (query: string) => {
            api.User.list({ query })
              .then(({ results }) => {
                // Users previously invited to the contest should not be shown
                // in the dropdown
                const addedUsers = new Set(
                  this.users.map((user) => user.username),
                );

                this.searchResultUsers = results
                  .filter((user) => !addedUsers.has(user.key))
                  .map(({ key, value }: types.ListItem) => ({
                    key,
                    value: `${ui.escape(key)} (<strong>${ui.escape(
                      value,
                    )}</strong>)`,
                  }));
              })
              .catch(ui.apiError);
          },
          'update-search-result-teams-groups': (query: string) => {
            api.TeamsGroup.list({
              query,
            })
              .then((data) => {
                this.searchResultTeamsGroups = data.map(
                  ({ key, value }: { key: string; value: string }) => ({
                    key,
                    value: `${ui.escape(value)} (<strong>${ui.escape(
                      key,
                    )}</strong>)`,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'update-contest': ({
            contest,
            teamsGroupAlias,
          }: {
            contest: types.ContestAdminDetails;
            teamsGroupAlias?: string;
          }): void => {
            api.Contest.update({
              ...contest,
              contest_alias: contest.alias,
              alias: null,
              teams_group_alias: teamsGroupAlias,
              contest_for_teams: !!teamsGroupAlias,
            })
              .then((data) => {
                if (teamsGroupAlias && data.teamsGroupName) {
                  this.teamsGroup = {
                    alias: teamsGroupAlias,
                    name: data.teamsGroupName,
                  };
                }
                this.details.title = data.title;
                ui.success(
                  ui.formatString(T.contestEditContestEdited, {
                    alias: contest.alias,
                  }),
                );
              })
              .catch(ui.apiError);
          },
          'add-problem': ({
            problem,
            isUpdate = false,
          }: {
            problem: types.ProblemsetProblem;
            isUpdate: boolean;
          }) => {
            api.Contest.addProblem({
              contest_alias: payload.details.alias,
              order_in_contest: problem.order,
              problem_alias: problem.alias,
              points: problem.points,
              commit: problem.commit,
            })
              .then((data) => {
                this.refreshProblems(true);
                if (isUpdate) {
                  ui.success(T.problemSuccessfullyUpdated);
                  return;
                }
                if (data.solutionStatus === 'not_found') {
                  ui.success(T.problemSuccessfullyAdded);
                } else {
                  ui.warning(T.warningPublicSolution);
                }
              })
              .catch(ui.apiError);
          },
          'get-versions': ({
            request,
            target,
          }: {
            request: { problemAlias: string };
            target: {
              versionLog: types.ProblemVersion[];
              problems: types.ProblemsetProblem[];
              selectedRevision: types.ProblemVersion;
              publishedRevision: types.ProblemVersion;
            };
          }) => {
            api.Problem.versions({
              problem_alias: request.problemAlias,
              problemset_id: payload.details.problemset_id,
            })
              .then((result) => {
                target.versionLog = result.log;
                let currentProblem = null;
                for (const problem of target.problems) {
                  if (problem.alias === request.problemAlias) {
                    currentProblem = problem;
                    break;
                  }
                }
                let publishedCommitHash = result.published;
                if (currentProblem != null) {
                  publishedCommitHash = currentProblem.commit;
                }
                for (const revision of result.log) {
                  if (publishedCommitHash === revision.commit) {
                    target.selectedRevision = target.publishedRevision = revision;
                    break;
                  }
                }
              })
              .catch(ui.apiError);
          },
          'remove-problem': (problemAlias: string) => {
            api.Contest.removeProblem({
              contest_alias: payload.details.alias,
              problem_alias: problemAlias,
            })
              .then(() => {
                ui.success(T.problemSuccessfullyRemoved);
                this.refreshProblems(false);
              })
              .catch(ui.apiError);
          },
          'runs-diff': (
            problemAlias: string,
            versionsComponent: types.CommitRunsDiff,
            selectedCommit: types.ProblemVersion,
          ) => {
            api.Contest.runsDiff({
              problem_alias: problemAlias,
              contest_alias: payload.details.alias,
              version: selectedCommit.version,
            })
              .then((response) => {
                Vue.set(
                  versionsComponent.runsDiff,
                  selectedCommit.version,
                  response.diff,
                );
              })
              .catch(ui.apiError);
          },
          'update-admission-mode': ({
            admissionMode,
            defaultShowAllContestantsInScoreboard,
          }: {
            admissionMode: string;
            defaultShowAllContestantsInScoreboard: boolean;
          }) => {
            api.Contest.update({
              contest_alias: payload.details.alias,
              admission_mode: admissionMode,
              default_show_all_contestants_in_scoreboard: defaultShowAllContestantsInScoreboard,
            })
              .then(() => {
                contestEdit.details.admission_mode = admissionMode;
                contestEdit.details.default_show_all_contestants_in_scoreboard = defaultShowAllContestantsInScoreboard;
                ui.success(
                  ui.formatString(T.contestEditContestEdited, {
                    alias: payload.details.alias,
                  }),
                );
                this.refreshDetails();
                if (admissionMode === 'registration') {
                  this.refreshRequests();
                }
              })
              .catch(ui.apiError);
          },
          'add-user': (contestants: string[]) => {
            Promise.allSettled(
              contestants.map((user: string) =>
                api.Contest.addUser({
                  contest_alias: payload.details.alias,
                  usernameOrEmail: user,
                }).catch(() => Promise.reject(user)),
              ),
            )
              .then((results) => {
                const contestantsWithError: string[] = results
                  .filter(
                    (result): result is PromiseRejectedResult =>
                      result.status === 'rejected',
                  )
                  .map((result) => result.reason);
                this.refreshUsers();
                this.refreshRequests();
                if (!contestantsWithError.length) {
                  ui.success(
                    contestants.length === 1
                      ? T.singleUserAddSuccess
                      : T.bulkUserAddSuccess,
                  );
                } else {
                  ui.error(
                    ui.formatString(T.bulkUserAddError, {
                      userEmail: contestantsWithError.join('<br>'),
                    }),
                  );
                }
              })
              .catch(ui.ignoreError);
          },
          'remove-user': (contestant: types.ContestUser) => {
            api.Contest.removeUser({
              contest_alias: payload.details.alias,
              usernameOrEmail: contestant.username,
            })
              .then(() => {
                ui.success(T.userRemoveSuccess);
                this.refreshUsers();
              })
              .catch(ui.apiError);
          },
          'save-end-time': (user: types.ContestUser) => {
            if (user.end_time === undefined) {
              return;
            }
            api.Contest.updateEndTimeForIdentity({
              contest_alias: payload.details.alias,
              username: user.username,
              end_time: user.end_time,
            })
              .then(() => {
                ui.success(T.userEndTimeUpdatedSuccessfully);
              })
              .catch(ui.apiError);
          },
          'accept-request': ({ username }: { username: string }) => {
            this.arbitrateRequest(username, true);
          },
          'deny-request': ({
            username,
            resolutionText,
          }: {
            username: string;
            resolutionText: null | string;
          }) => {
            this.arbitrateRequest(username, false, resolutionText);
          },
          'add-admin': (username: string) => {
            api.Contest.addAdmin({
              contest_alias: payload.details.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminAdded);
                this.refreshAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-admin': (username: string) => {
            api.Contest.removeAdmin({
              contest_alias: payload.details.alias,
              usernameOrEmail: username,
            })
              .then(() => {
                ui.success(T.adminRemoved);
                this.refreshAdmins();
              })
              .catch(ui.apiError);
          },
          'add-group-admin': (groupAlias: string) => {
            api.Contest.addGroupAdmin({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminAdded);
                this.refreshGroupAdmins();
              })
              .catch(ui.apiError);
          },
          'remove-group-admin': (groupAlias: string) => {
            api.Contest.removeGroupAdmin({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.groupAdminRemoved);
                this.refreshGroupAdmins();
              })
              .catch(ui.apiError);
          },
          'add-group': (groupAlias: string) => {
            api.Contest.addGroup({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.contestGroupAdded);
                this.refreshGroups();
              })
              .catch(ui.apiError);
          },
          'remove-group': (groupAlias: string) => {
            api.Contest.removeGroup({
              contest_alias: payload.details.alias,
              group: groupAlias,
            })
              .then(() => {
                ui.success(T.contestGroupRemoved);
                this.refreshGroups();
              })
              .catch(ui.apiError);
          },
          'clone-contest': (
            title: string,
            alias: string,
            description: string,
            startTime: Date,
          ) => {
            api.Contest.clone({
              contest_alias: payload.details.alias,
              title: title,
              alias: alias,
              description: description,
              start_time: startTime.getTime() / 1000,
            })
              .then(() => {
                ui.success(T.contestEditContestClonedSuccessfully);
              })
              .catch(ui.apiError);
          },
          'archive-contest': (contestAlias: string, archive: string) => {
            api.Contest.archive({ contest_alias: contestAlias, archive })
              .then(() => {
                if (archive) {
                  ui.success(T.contestEditArchivedSuccess);
                  return;
                }
                ui.success(T.contestEditUnarchivedSuccess);
              })
              .catch(ui.apiError);
          },
          'replace-teams-group': ({
            alias,
            name,
          }: {
            alias: string;
            name: string;
          }) => {
            api.Contest.replaceTeamsGroup({
              contest_alias: payload.details.alias,
              teams_group_alias: alias,
            })
              .then(() => {
                this.teamsGroup = { alias, name };
                ui.success(T.contestEditTeamsGroupReplaced);
              })
              .catch(ui.apiError);
          },
          'language-remove-blocked': (language: string) => {
            ui.warning(
              ui.formatString(T.contestNewFormLanguageRemoveBlockedWarning, {
                language: language,
              }),
            );
          },
          'download-csv-scoreboard': (contestAlias: string) => {
            api.Contest.scoreboard({ contest_alias: contestAlias })
              .then((result) => {
                const table: TableCell[][] = [];
                const header = [
                  T.profileContestsTablePlace,
                  T.profileUsername,
                  T.profileName,
                ];
                for (let index = 0; index < result.problems.length; index++) {
                  header.push(ui.columnName(index));
                }
                header.push('Total');
                table.push(header);
                for (const user of result.ranking) {
                  const row: TableCell[] = [
                    user.place || '',
                    user.username,
                    user.name || '',
                  ];
                  for (const problem of user.problems) {
                    if (problem.runs > 0) {
                      row.push(problem.points.toFixed(2));
                    } else {
                      row.push('');
                    }
                  }
                  row.push(user.total.points.toFixed(2));
                  table.push(row);
                }
                this.downloadCsvFile(`${contestAlias}_scoreboard.csv`, table);
              })
              .catch(ui.apiError);
          },
          'show-copy-message': (): void => {
            ui.success(T.contestEditContestLinkCopiedToClipboard);
          },
          'generate-certificates': (certificateCutoff: number) => {
            api.Certificate.generateContestCertificates({
              certificates_cutoff: certificateCutoff,
              contest_alias: payload.details.alias,
            })
              .then(() => {
                contestEdit.certificatesDetails.certificatesStatus = 'queued';
                contestEdit.certificatesDetails.certificateCutoff = certificateCutoff;
                ui.success(T.contestCertificatesGenerateSuccessfully);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
  });
});

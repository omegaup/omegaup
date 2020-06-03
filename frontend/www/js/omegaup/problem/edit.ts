import { OmegaUp, omegaup } from '../omegaup';
import { types } from '../api_types';
import T from '../lang';
import Vue from 'vue';
import problem_Edit from '../components/problem/Edit.vue';
import * as ui from '../ui';
import * as api from '../api';
import * as Markdown from '@/third_party/js/pagedown/Markdown.Editor.js';
import * as markdown from '../markdown';

OmegaUp.on('ready', () => {
  const payload = types.payloadParsers.ProblemEditPayload();
  if (payload.statusError) {
    ui.error(payload.statusError);
  }
  if (payload.statusSuccess) {
    ui.success(T.problemEditUpdatedSuccessfully);
  }
  const problemEdit = new Vue({
    el: '#main-container',
    render: function(createElement) {
      return createElement('omegaup-problem-edit', {
        props: {
          data: payload,
          originalVisibility: payload.visibility,
          initialAdmins: this.initialAdmins,
          initialGroups: this.initialGroups,
          initialLanguage: this.initialLanguage,
          log: this.log,
          publishedRevision: this.publishedRevision,
          value: this.publishedRevision,
          markdownContents: this.markdownContents,
          markdownPreview: this.markdownPreview,
          markdownSolutionContents: this.markdownSolutionContents,
          markdownSolutionPreview: this.markdownSolutionPreview,
        },
        on: {
          'update-markdown-contents': (
            statements: types.Statements,
            language: string,
            currentMarkdown: any,
            markdownType: string,
          ) => {
            // First update markdown contents to current markdown, otherwise
            // component won't detect any change if two different language
            // solutions are the same.
            if (markdownType === 'statements') {
              problemEdit.markdownContents = currentMarkdown;
              if (statements.hasOwnProperty(language)) {
                problemEdit.updateAndRefresh(
                  statements[language],
                  markdownType,
                );
                return;
              }
              api.Problem.details({
                problem_alias: payload.alias,
                statement_type: 'markdown',
                show_solvers: false,
                lang: language,
              })
                .then(response => {
                  if (!response.exists || !response.statement) {
                    return;
                  }
                  if (response.statement.language !== language) {
                    response.statement.markdown = '';
                  }
                  problemEdit.statements[language] =
                    response.statement.markdown;
                  problemEdit.updateAndRefresh(
                    response.statement.markdown,
                    markdownType,
                  );
                })
                .catch(ui.apiError);
            } else {
              problemEdit.markdownSolutionContents = currentMarkdown;
              if (statements.hasOwnProperty(language)) {
                problemEdit.updateAndRefresh(
                  statements[language],
                  markdownType,
                );
                return;
              }
              api.Problem.solution({
                problem_alias: payload.alias,
                lang: language,
              })
                .then(response => {
                  if (!response.exists || !response.solution) {
                    return;
                  }
                  if (response.solution.language !== language) {
                    response.solution.markdown = '';
                  }
                  problemEdit.solutions[language] = response.solution.markdown;
                  problemEdit.updateAndRefresh(
                    response.solution.markdown,
                    markdownType,
                  );
                })
                .catch(ui.apiError);
            }
          },
          'add-tag': (alias: string, tagname: string, isPublic: boolean) => {
            api.Problem.addTag({
              problem_alias: alias,
              name: tagname,
              public: isPublic,
            })
              .then(response => {
                ui.success(T.tagAdded);
              })
              .catch(ui.apiError);
          },
          'remove-tag': (alias: string, tagname: string) => {
            api.Problem.removeTag({
              problem_alias: alias,
              name: tagname,
            })
              .then(response => {
                ui.success(T.tagRemoved);
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
              .then(response => {
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
              .then(response => {
                problemEdit.publishedRevision = selectedRevision;
                ui.success(T.problemVersionUpdated);
              })
              .catch(ui.apiError);
          },
          'runs-diff': (
            versions: types.CommitRunsDiff,
            selectedCommit: omegaup.Commit,
          ) => {
            api.Problem.runsDiff({
              problem_alias: payload.alias,
              version: selectedCommit.version,
            })
              .then(response => {
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
              .then(response => {
                window.location.href = '/problem/mine/';
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    mounted() {
      const markdownConverter = markdown.markdownConverter({
        preview: true,
        imageMapping: {},
      });
      this.markdownEditor = new Markdown.Editor(
        markdownConverter,
        '-statements',
      );
      this.markdownEditor.run();
    },
    methods: {
      refreshProblemAdmins: (): void => {
        api.Problem.admins({ problem_alias: payload.alias })
          .then(data => {
            problemEdit.initialAdmins = data.admins;
            problemEdit.initialGroups = data.group_admins;
          })
          .catch(ui.apiError);
      },
      updateAndRefresh: (markdown: string, markdownType: string): void => {
        if (markdown === null) {
          return;
        }
        if (markdownType === 'statements') {
          problemEdit.markdownContents = markdown;
          problemEdit.markdownPreview = problemEdit.markdownEditor
            .getConverter()
            .makeHtml(markdown);
        }
        if (markdownType === 'solutions') {
          problemEdit.markdownSolutionContents = markdown;
          problemEdit.markdownSolutionPreview = problemEdit.markdownEditor
            .getConverter()
            .makeHtml(markdown);
        }
      },
      getInitialContents: (): void => {
        const lang = payload.statement.language;
        problemEdit.initialLanguage = lang;
        problemEdit.statements[lang] = payload.statement.markdown;
        problemEdit.solutions[lang] = payload.solution.markdown;
        problemEdit.updateAndRefresh(payload.statement.markdown, 'statements');
        problemEdit.updateAndRefresh(payload.solution.markdown, 'solutions');
      },
    },
    data: {
      initialAdmins: payload.admins,
      initialGroups: payload.groupAdmins,
      initialLanguage: '',
      log: [],
      publishedRevision: {},
      markdownContents: '',
      markdownPreview: '',
      markdownSolutionContents: '',
      markdownSolutionPreview: '',
      markdownEditor: <Markdown.Editor>{},
      solutions: <types.Statements>{},
      statements: <types.Statements>{},
    },
    components: {
      'omegaup-problem-edit': problem_Edit,
    },
  });

  const imageMapping = {};
  const markdownConverter = markdown.markdownConverter({
    preview: true,
    imageMapping: imageMapping,
  });
  problemEdit.getInitialContents();
});

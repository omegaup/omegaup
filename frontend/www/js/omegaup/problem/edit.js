import Vue from 'vue';
import problem_Versions from '../components/problem/Versions.vue';
import problem_StatementEdit from '../components/problem/StatementEdit.vue';
import problem_Settings from '../components/problem/Settings.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import * as api from '../api';
import * as markdown from '../markdown';
import * as time from '../time';
import * as typeahead from '../typeahead';
import * as ui from '../ui';

OmegaUp.on('ready', function() {
  var chosenLanguage = null;
  var statements = {};

  if (window.location.hash) {
    $('#sections')
      .find('a[href="' + window.location.hash + '"]')
      .tab('show');
  }
  const payload = JSON.parse(
    document.getElementById('problem-edit-payload').innerText,
  );

  $('.page-header h1 span').html(
    `${T.problemEditEditProblem} ${ui.escape(payload.title)}`,
  );
  $('.page-header h1 small').html(
    `&ndash; <a href="/arena/problem/${payload.alias}/">${T.problemEditGoToProblem}</a>`,
  );

  $('#sections').on('click', 'a', function(e) {
    e.preventDefault();
    // add this line
    window.location.hash = $(this).attr('href');
    $(this).tab('show');
  });

  var problemAlias = $('#problem-alias').val();

  // Add typeaheads
  typeahead.tagTypeahead($('input[name=tag_name]'));

  $('#download form').on('submit', function() {
    window.location = `/api/problem/download/problem_alias/${ui.escape(
      problemAlias,
    )}/`;
    return false;
  });

  $('#delete form').on('submit', function(event) {
    event.preventDefault();
    api.Problem.delete({ problem_alias: problemAlias })
      .then(function(response) {
        window.location = '/problem/mine/';
      })
      .catch(ui.apiError);
    return false;
  });

  const problemVersions = new Vue({
    el: '#version div.panel div',
    render: function(createElement) {
      return createElement('omegaup-problem-versions', {
        props: {
          log: this.log,
          publishedRevision: this.publishedRevision,
          value: this.publishedRevision,
          showFooter: true,
        },
        on: {
          'select-version': function(selectedRevision, updatePublished) {
            api.Problem.selectVersion({
              problem_alias: problemAlias,
              commit: selectedRevision.commit,
              update_published: updatePublished,
            })
              .then(function(response) {
                problemVersions.publishedRevision = selectedRevision;
                ui.success(T.problemVersionUpdated);
              })
              .catch(ui.apiError);
          },
          'runs-diff': function(versions, selectedCommit) {
            api.Problem.runsDiff({
              problem_alias: problemAlias,
              version: selectedCommit.version,
            })
              .then(function(response) {
                Vue.set(
                  versions.runsDiff,
                  selectedCommit.version,
                  response.diff,
                );
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    data: {
      log: [],
      publishedRevision: {},
    },
    components: {
      'omegaup-problem-versions': problem_Versions,
    },
  });
  api.Problem.versions({ problem_alias: problemAlias })
    .then(function(result) {
      problemVersions.log = result.log;
      for (const revision of result.log) {
        if (result.published == revision.commit) {
          problemVersions.publishedRevision = revision;
          break;
        }
      }
    })
    .catch(ui.apiError);

  const solutionEdit = new Vue({
    el: '#solution-edit',
    render: function(createElement) {
      return createElement('omegaup-problem-solution-edit', {
        props: {
          markdownContents: this.markdownContents,
          markdownPreview: this.markdownPreview,
          initialLanguage: this.initialLanguage,
          markdownType: 'solutions',
          title: payload.title,
        },
        on: {
          'update-markdown-contents': function(
            solutions,
            language,
            currentMarkdown,
          ) {
            // First update markdown contents to current markdown, otherwise
            // component won't detect any change if two different language
            // solutions are the same.
            solutionEdit.markdownContents = currentMarkdown;
            if (solutions.hasOwnProperty(language)) {
              solutionEdit.updateAndRefresh(solutions[language]);
              return;
            }
            api.Problem.solution({
              problem_alias: problemAlias,
              lang: language,
            })
              .then(function(response) {
                if (!response.exists || !response.solution) {
                  return;
                }
                if (response.solution.language !== language) {
                  response.solution.markdown = '';
                }
                solutionEdit.solutions[language] = response.solution.markdown;
                solutionEdit.updateAndRefresh(response.solution.markdown);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    mounted: function() {
      const markdownConverter = markdown.markdownConverter({
        preview: true,
        imageMapping: {},
      });
      this.markdownEditor = new Markdown.Editor(
        markdownConverter,
        '-solutions',
      );
      this.markdownEditor.run();
    },
    data: {
      markdownContents: null,
      markdownPreview: '',
      markdownEditor: null,
      initialLanguage: null,
      solutions: {},
    },
    methods: {
      updateAndRefresh(markdown) {
        this.markdownContents = markdown;
        this.markdownPreview = this.markdownEditor
          .getConverter()
          .makeHtml(markdown);
      },
      getInitialContents() {
        let self = this;
        api.Problem.solution({
          problem_alias: problemAlias,
        })
          .then(function(response) {
            if (!response.exists || !response.solution) {
              return;
            }
            const lang = response.solution.language;
            self.initialLanguage = lang;
            self.solutions[lang] = response.solution.markdown;
            self.updateAndRefresh(response.solution.markdown);
          })
          .catch(ui.apiError);
      },
    },
    components: {
      'omegaup-problem-solution-edit': problem_StatementEdit,
    },
  });
  solutionEdit.getInitialContents();

  const markdownPayload = JSON.parse(
    document.getElementById('problem-markdown-payload').innerText,
  );
  const problemMarkdown = new Vue({
    el: '#markdown div',
    render: function(createElement) {
      return createElement('omegaup-problem-markdown', {
        props: {
          markdownContents: this.markdownContents,
          markdownPreview: this.markdownPreview,
          initialLanguage: this.initialLanguage,
          markdownType: 'statements',
          alias: markdownPayload.alias,
          title: markdownPayload.title,
          source: markdownPayload.source,
          username: this.username,
          name: this.name,
          classname: this.classname,
        },
        on: {
          'update-markdown-contents': (
            statements,
            language,
            currentMarkdown,
          ) => {
            // First update markdown contents to current markdown, otherwise
            // component won't detect any change if two different language
            // statements are the same.
            problemMarkdown.markdownContents = currentMarkdown;
            if (statements.hasOwnProperty(language)) {
              problemMarkdown.updateAndRefresh(statements[language]);
              return;
            }
            api.Problem.details({
              problem_alias: markdownPayload.alias,
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
                problemMarkdown.statements[language] =
                  response.statement.markdown;
                problemMarkdown.updateAndRefresh(response.statement.markdown);
              })
              .catch(ui.apiError);
          },
        },
      });
    },
    mounted: function() {
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
    data: {
      markdownContents: null,
      markdownPreview: '',
      markdownEditor: null,
      initialLanguage: null,
      username: markdownPayload.problemsetter
        ? markdownPayload.problemsetter.username
        : null,
      name: markdownPayload.problemsetter
        ? markdownPayload.problemsetter.name
        : null,
      classname: markdownPayload.problemsetter
        ? markdownPayload.problemsetter.classname
        : null,
      statements: {},
    },
    methods: {
      updateAndRefresh(markdown) {
        this.markdownContents = markdown;
        this.markdownPreview = this.markdownEditor
          .getConverter()
          .makeHtml(markdown);
      },
      getInitialContents() {
        const self = this;
        const lang = markdownPayload.statement.language;
        self.initialLanguage = lang;
        self.statements[lang] = markdownPayload.statement.markdown;
        self.updateAndRefresh(markdownPayload.statement.markdown);
      },
    },
    components: {
      'omegaup-problem-markdown': problem_StatementEdit,
    },
  });
  problemMarkdown.getInitialContents();

  var imageMapping = {};
  var markdownConverter = markdown.markdownConverter({
    preview: true,
    imageMapping: imageMapping,
  });

  function problemCallback(problem) {
    // Extend the current mapping with any new images.
    for (var filename in problem.statement.images) {
      if (
        !problem.statement.images.hasOwnProperty(filename) ||
        imageMapping.hasOwnProperty(filename)
      ) {
        continue;
      }
      imageMapping[filename] = problem.statement.images[filename];
    }
    if (problem.slow == 1) {
      $('.slow-warning').show();
    }
  }
  problemCallback(payload);
});

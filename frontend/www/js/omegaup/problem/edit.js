import Vue from 'vue';
import problem_Versions from '../components/problem/Versions.vue';
import problem_StatementEdit from '../components/problem/StatementEdit.vue';
import problem_Settings from '../components/problem/Settings.vue';
import { OmegaUp } from '../omegaup';
import T from '../lang';
import API from '../api.js';
import * as UI from '../ui';

OmegaUp.on('ready', function() {
  var chosenLanguage = null;
  var statements = {};

  if (window.location.hash) {
    $('#sections')
      .find('a[href="' + window.location.hash + '"]')
      .tab('show');
  }
  const payload = JSON.parse(
    document.getElementById('problem-payload').innerText,
  );
  let problemSettings = new Vue({
    el: '#problem-settings',
    render: function(createElement) {
      return createElement('omegaup-problem-settings', {
        props: {
          timeLimit: this.timeLimit,
          extraWallTime: this.extraWallTime,
          memoryLimit: this.memoryLimit,
          outputLimit: this.outputLimit,
          inputLimit: this.inputLimit,
          overallWallTimeLimit: this.overallWallTimeLimit,
          validatorTimeLimit: this.validatorTimeLimit,
          initialLanguage: this.languages,
          validLanguages: this.validLanguages,
          initialValidator: this.validator,
          validatorTypes: this.validatorTypes,
        },
      });
    },
    data: {
      timeLimit: 0,
      extraWallTime: 0,
      memoryLimit: 0,
      outputLimit: 0,
      inputLimit: 0,
      overallWallTimeLimit: 0,
      validatorTimeLimit: 0,
      validLanguages: payload.validLanguages,
      validatorTypes: payload.validatorTypes,
      validator: '',
      languages: '',
    },
    components: {
      'omegaup-problem-settings': problem_Settings,
    },
  });

  $('#sections').on('click', 'a', function(e) {
    e.preventDefault();
    // add this line
    window.location.hash = $(this).attr('href');
    $(this).tab('show');
  });

  var problemAlias = $('#problem-alias').val();
  refreshEditForm(problemAlias);

  // Add typeaheads
  refreshProblemAdmins();
  UI.userTypeahead($('#username-admin'));
  UI.typeahead($('#groupalias-admin'), API.Group.list, function(event, val) {
    $(event.target).attr('data-alias', val.value);
  });

  refreshProblemTags();

  API.Tag.list({ query: '' })
    .then(function(response) {
      var tags = {};
      $('#problem-tags a').each(function(index) {
        tags[$(this).html()] = true;
      });
      response.forEach(function(e) {
        if (tags.hasOwnProperty(e.name)) {
          return;
        }
        $('#tags .tag-list').append(
          $('<a></a>')
            .attr('href', '#tags')
            .attr('data-key', e.name)
            .addClass('tag')
            .addClass('pull-left')
            .text(T.hasOwnProperty(e.name) ? T[e.name] : e.name),
        );
      });
      $(document).on('click', '.tag', function(event) {
        var tagname = $(this).data('key');
        var isPublic = $('#tag-public').val();
        $(this).remove();
        API.Problem.addTag({
          problem_alias: problemAlias,
          name: tagname,
          public: isPublic,
        })
          .then(function(response) {
            UI.success('Tag successfully added!');
            $('div.post.footer').show();

            refreshProblemTags();
          })
          .catch(UI.apiError);

        return false; // Prevent refresh
      });
    })
    .catch(UI.apiError);

  $('#tag-name')
    .typeahead(
      {
        minLength: 2,
        highlight: true,
      },
      {
        source: UI.typeaheadWrapper(API.Tag.list),
        async: true,
        display: 'name',
      },
    )
    .on('typeahead:select', function(event, val) {
      $(event.target).val(val.name);
    });

  $('#add-admin-form').on('submit', function() {
    var username = $('#username-admin').val();

    API.Problem.addAdmin({
      problem_alias: problemAlias,
      usernameOrEmail: username,
    })
      .then(function(response) {
        UI.success(T.adminAdded);
        $('div.post.footer').show();
        refreshProblemAdmins();
      })
      .catch(UI.apiError);

    return false; // Prevent refresh
  });

  $('#toggle-site-admins').on('change', function() {
    if ($(this).is(':checked')) {
      $('#problem-admins .site-admin').show();
    } else {
      $('#problem-admins .site-admin').hide();
    }
  });

  $('#add-group-admin-form').on('submit', function() {
    API.Problem.addGroupAdmin({
      problem_alias: problemAlias,
      group: $('#groupalias-admin').attr('data-alias'),
    })
      .then(function(response) {
        UI.success(T.groupAdminAdded);
        $('div.post.footer').show();

        refreshProblemAdmins();
      })
      .catch(UI.apiError);

    return false; // Prevent refresh
  });

  $('#download form').on('submit', function() {
    window.location =
      '/api/problem/download/problem_alias/' + UI.escape(problemAlias) + '/';
    return false;
  });

  $('#delete form').on('submit', function(event) {
    event.preventDefault();
    API.Problem.delete({ problem_alias: problemAlias })
      .then(function(response) {
        window.location = '/problem/mine/';
      })
      .catch(UI.apiError);
    return false;
  });

  $('#markdown form').on('submit', function() {
    var promises = [];
    for (var lang in statements) {
      if (!statements.hasOwnProperty(lang)) continue;
      if (typeof statements[lang].current === 'undefined') continue;
      if (statements[lang].current === statements[lang].original) continue;
      promises.push(
        new Promise(function(resolve, reject) {
          API.Problem.updateStatement({
            problem_alias: problemAlias,
            statement: statements[lang].current,
            message: $('#markdown-message').val(),
            lang: lang,
          })
            .then(function(response) {
              resolve(response);
            })
            .catch(T.editFieldRequired);
        }),
      );
    }

    $('.has-error').removeClass('has-error');
    if ($('#markdown-message').val() == '') {
      UI.error(T.editFieldRequired);
      $('#markdown-message-group').addClass('has-error');
      return false;
    }

    Promise.all(promises)
      .then(function(results) {
        UI.success(T.problemEditUpdatedSuccessfully);
        for (var lang in statements) {
          statements[lang].original = statements[lang].current;
        }
      })
      .catch(UI.apiError);
    return false;
  });

  function refreshProblemAdmins() {
    API.Problem.admins({ problem_alias: problemAlias })
      .then(function(admins) {
        $('#problem-admins').empty();
        // Got the contests, lets populate the dropdown with them
        for (var i = 0; i < admins.admins.length; i++) {
          var admin = admins.admins[i];
          var siteAdmin = admin.role == 'site-admin' ? admin.role : '';
          $('#problem-admins').append(
            $('<tr></tr>')
              .addClass(siteAdmin)
              .append(
                $('<td></td>').append(
                  $('<a></a>')
                    .attr('href', '/profile/' + admin.username + '/')
                    .text(admin.username),
                ),
              )
              .append($('<td></td>').text(admin.role))
              .append(
                admin.role != 'admin'
                  ? $('<td></td>')
                  : $(
                      '<td><button type="button" class="close">' +
                        '&times;</button></td>',
                    ).on(
                      'click',
                      (function(username) {
                        return function(e) {
                          API.Problem.removeAdmin({
                            problem_alias: problemAlias,
                            usernameOrEmail: username,
                          })
                            .then(function(response) {
                              UI.success(T.adminRemoved);
                              $('div.post.footer').show();
                              var tr = e.target.parentElement.parentElement;
                              $(tr).remove();
                            })
                            .catch(UI.apiError);
                        };
                      })(admin.username),
                    ),
              ),
          );
        }
        $('#problem-group-admins').empty();
        // Got the contests, lets populate the dropdown with them
        for (var i = 0; i < admins.group_admins.length; i++) {
          var group_admin = admins.group_admins[i];
          $('#problem-group-admins').append(
            $('<tr></tr>')
              .append(
                $('<td></td>').append(
                  $('<a></a>')
                    .attr('href', '/group/' + group_admin.alias + '/edit/')
                    .text(group_admin.name),
                ),
              )
              .append($('<td></td>').text(group_admin.role))
              .append(
                group_admin.role != 'admin'
                  ? $('<td></td>')
                  : $(
                      '<td><button type="button" class="close">' +
                        '&times;</button></td>',
                    ).on(
                      'click',
                      (function(alias) {
                        return function(e) {
                          API.Problem.removeGroupAdmin({
                            problem_alias: problemAlias,
                            group: alias,
                          })
                            .then(function(response) {
                              UI.success(T.groupAdminRemoved);
                              $('div.post.footer').show();
                              var tr = e.target.parentElement.parentElement;
                              $(tr).remove();
                            })
                            .catch(UI.apiError);
                        };
                      })(group_admin.alias),
                    ),
              ),
          );
        }

        $('#problem-admins .site-admin').hide();
      })
      .catch(UI.apiError);
  }

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
            API.Problem.selectVersion({
              problem_alias: problemAlias,
              commit: selectedRevision.commit,
              update_published: updatePublished,
            })
              .then(function(response) {
                problemVersions.publishedRevision = selectedRevision;
                UI.success(T.problemVersionUpdated);
              })
              .catch(UI.apiError);
          },
          'runs-diff': function(versions, selectedCommit) {
            API.Problem.runsDiff({
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
              .catch(UI.apiError);
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
  API.Problem.versions({ problem_alias: problemAlias })
    .then(function(result) {
      problemVersions.log = result.log;
      for (const revision of result.log) {
        if (result.published == revision.commit) {
          problemVersions.publishedRevision = revision;
          break;
        }
      }
    })
    .catch(UI.apiError);

  const solutionEdit = new Vue({
    el: '#solution-edit',
    render: function(createElement) {
      return createElement('omegaup-problem-solution-edit', {
        props: {
          markdownContents: this.markdownContents,
          markdownPreview: this.markdownPreview,
          initialLanguage: this.initialLanguage,
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
            API.Problem.solution({
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
              .catch(UI.apiError);
          },
          'edit-solution': function(solutions, commitMessage, currentLanguage) {
            let promises = [];
            for (const lang in solutions) {
              if (!solutions.hasOwnProperty(lang)) continue;
              if (solutions[lang] === solutionEdit.solutions[lang]) continue;
              promises.push(
                new Promise(function(resolve, reject) {
                  API.Problem.updateSolution({
                    problem_alias: problemAlias,
                    solution: solutions[lang],
                    message: commitMessage,
                    lang: lang,
                  })
                    .then(resolve)
                    .catch(UI.apiError);
                }),
              );
            }
            Promise.all(promises)
              .then(function() {
                UI.success(T.problemEditUpdatedSuccessfully);
              })
              .catch(function(error) {
                UI.apiError(error);
              });
          },
        },
      });
    },
    mounted: function() {
      const markdownConverter = UI.markdownConverter({
        preview: true,
        imageMapping: {},
      });
      this.markdownEditor = new Markdown.Editor(markdownConverter, '-solution');
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
        API.Problem.solution({
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
          .catch(UI.apiError);
      },
    },
    components: {
      'omegaup-problem-solution-edit': problem_StatementEdit,
    },
  });
  solutionEdit.getInitialContents();

  $('#tags form').on('submit', function() {
    var tagname = $('#tag-name').val();
    var isPublic = $('#tag-public').val();

    API.Problem.addTag({
      problem_alias: problemAlias,
      name: tagname,
      public: isPublic,
    })
      .then(function(response) {
        UI.success('Tag successfully added!');
        $('div.post.footer').show();

        refreshProblemTags();
      })
      .catch(UI.apiError);

    return false; // Prevent refresh
  });

  function refreshProblemTags() {
    API.Problem.tags({
      problem_alias: problemAlias,
      include_voted: false,
    })
      .then(function(result) {
        $('#problem-tags').empty();
        // Got the contests, lets populate the dropdown with them
        for (var i = 0; i < result.tags.length; i++) {
          var tag = result.tags[i];
          $('#problem-tags').append(
            $('<tr></tr>')
              .append(
                $('<td></td>').append(
                  $('<a></a>')
                    .attr('href', '/problem/?tag[]=' + tag.name)
                    .text(tag.name),
                ),
              )
              .append($('<td></td>').text(tag['public']))
              .append(
                $(
                  '<td><button type="button" class="close">' +
                    '&times;</button></td>',
                ).on(
                  'click',
                  (function(tagname) {
                    return function(e) {
                      API.Problem.removeTag({
                        problem_alias: problemAlias,
                        name: tagname,
                      })
                        .then(function(response) {
                          UI.success('Tag successfully removed!');
                          $('div.post.footer').show();
                          var tr = e.target.parentElement.parentElement;
                          $('#tags .tag-list').append(
                            '<a href="#tags" ' +
                              'class="tag pull-left">' +
                              $(tr)
                                .find('a')
                                .html() +
                              '</a>',
                          );
                          $(tr).remove();
                        })
                        .catch(UI.apiError);
                    };
                  })(tag.name),
                ),
              ),
          );
        }
      })
      .catch(UI.apiError);
  }

  var imageMapping = {};
  var markdownConverter = UI.markdownConverter({
    preview: true,
    imageMapping: imageMapping,
  });
  var markdownEditor = new Markdown.Editor(markdownConverter, '-statement'); // Global.
  markdownEditor.run();

  function refreshEditForm(problemAlias) {
    if (problemAlias === '') {
      $('input[name=title]').val('');
      $('input[name=time_limit]').val('');
      $('input[name=validator_time_limit]').val('');
      $('input[name=overall_wall_time_limit]').val('');
      $('input[name=extra_wall_time]').val('');
      $('input[name=memory_limit]').val('');
      $('input[name=output_limit]').val('');
      $('input[name=input_limit]').val('');
      $('input[name=source]').val('');
      return;
    }

    API.Problem.details({
      problem_alias: problemAlias,
      statement_type: 'markdown',
    })
      .then(problemCallback)
      .catch(UI.apiError);
  }

  function problemCallback(problem) {
    $('.page-header h1 span').html(
      T.problemEditEditProblem + ' ' + UI.escape(problem.title),
    );
    $('.page-header h1 small').html(
      '&ndash; <a href="/arena/problem/' +
        problemAlias +
        '/">' +
        T.problemEditGoToProblem +
        '</a>',
    );

    $('#statement-preview .title').html(UI.escape(problem.title));
    problemSettings.languages = problem.languages.sort().join();
    $('input[name=title]').val(problem.title);
    problemSettings.timeLimit = UI.parseDuration(
      problem.settings.limits.TimeLimit,
    );

    if (
      problem.settings.validator.custom_validator &&
      problem.settings.validator.custom_validator.limits
    ) {
      problemSettings.validatorTimeLimit = UI.parseDuration(
        problem.settings.validator.custom_validator.limits.TimeLimit,
      );
    } else {
      problemSettings.validatorTimeLimit = 0;
    }
    problemSettings.overallWallTimeLimit = UI.parseDuration(
      problem.settings.limits.OverallWallTimeLimit,
    );
    problemSettings.extraWallTime = UI.parseDuration(
      problem.settings.limits.ExtraWallTime,
    );
    problemSettings.memoryLimit = problem.settings.limits.MemoryLimit / 1024;
    problemSettings.outputLimit = problem.settings.limits.OutputLimit;
    problemSettings.inputLimit = problem.input_limit;
    $('input[name=source]').val(problem.source);
    $('#statement-preview .source').html(UI.escape(problem.source));
    $('#statement-preview .problemsetter')
      .attr('href', '/profile/' + problem.problemsetter.username + '/')
      .html(UI.escape(problem.problemsetter.name));
    $(
      'input[name=email_clarifications][value=' +
        (problem.email_clarifications ? '1' : '0') +
        ']',
    ).attr('checked', 1);
    problemSettings.validator = problem.settings.validator.name;
    var visibility = Math.max(0, Math.min(1, problem.visibility));
    $('input[name=visibility][value=' + visibility + ']').attr('checked', 1);
    if (visibility != problem.visibility) {
      // The problem is banned or promoted, so the user isn't allowed to
      // make change visibility.
      $('input[name=visibility]').attr('disabled', 1);
    }
    $('input[name=alias]').val(problemAlias);

    if (
      chosenLanguage == null ||
      chosenLanguage == problem.statement.language
    ) {
      chosenLanguage = problem.statement.language;
      if (typeof statements[chosenLanguage] == 'undefined') {
        statements[chosenLanguage] = {
          original: problem.statement.markdown,
          current: problem.statement.markdown,
        };
      }
      $('#wmd-input-statement').val(statements[chosenLanguage].current);
      $('#statement-language').val(problem.statement.language);
    } else {
      $('#wmd-input-statement').val('');
    }
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
    markdownEditor.refreshPreview();
    if (problem.slow == 1) {
      $('.slow-warning').show();
    }
  }

  $('#statement-preview-link').on('show.bs.tab', function(e) {
    MathJax.Hub.Queue(['Typeset', MathJax.Hub, $('#wmd-preview').get(0)]);
  });

  $('#statement-language').on('change', function(e) {
    chosenLanguage = $('#statement-language').val();
    API.Problem.details({
      problem_alias: problemAlias,
      statement_type: 'markdown',
      show_solvers: false,
      lang: chosenLanguage,
    })
      .then(problemCallback)
      .catch(UI.apiError);
  });

  $('#wmd-input-statement').on('blur', function(e) {
    var currentLanguage = $('#statement-language').val();
    if (!statements.hasOwnProperty(currentLanguage)) {
      statements[currentLanguage] = {
        original: '',
        current: '',
      };
    }
    statements[currentLanguage].current = $(this).val();
  });
});

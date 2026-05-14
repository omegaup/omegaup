<template>
  <b-container fluid>
    <b-tabs content-class="mt-3" align="center">
      <b-tab :title="T.wordsProblem" active>
        <omegaup-problem-settings-summary
          :problem="problem"
          :show-edit-link="user.admin"
        ></omegaup-problem-settings-summary>
        <div v-if="problem.karel_problem" class="karel-js-link my-3">
          <a
            class="p-3"
            :href="`/karel.js/${
              problem.sample_input
                ? `#mundo:${encodeURIComponent(problem.sample_input)}`
                : ''
            }`"
            target="_blank"
          >
            {{ T.openInKarelJs }}
            <font-awesome-icon :icon="['fas', 'external-link-alt']" />
          </a>
        </div>
        <div class="mt-4 markdown">
          <omegaup-markdown
            ref="statement-markdown"
            :markdown="problem.statement.markdown"
            :source-mapping="problem.statement.sources"
            :image-mapping="problem.statement.images"
            :problem-settings="problem.settings"
            :full-width="true"
            @rendered="onProblemRendered"
          ></omegaup-markdown>
        </div>
        <hr class="my-3" />
        <div class="font-italic">
          {{
            ui.formatString(T.problemDetailsSource, {
              source: problem.source,
            })
          }}
        </div>
        <template v-if="problem.problemsetter">
          <div>
            {{ T.wordsProblemsetter }}:
            <omegaup-username
              :classname="problem.problemsetter.classname"
              :username="problem.problemsetter.username"
              :name="problem.problemsetter.name"
              :linkify="true"
            ></omegaup-username>
          </div>
          <div>
            {{
              ui.formatString(T.wordsUploadedOn, {
                date: time.formatDate(problem.problemsetter.creation_date),
              })
            }}
          </div>
          <!-- TODO: Add the nomination buttons -->
        </template>
        <omegaup-overlay
          v-if="user.loggedIn"
          :show-overlay="currentPopupDisplayed !== PopupDisplayed.None"
          @hide-overlay="onPopupDismissed"
        >
          <template #popup>
            <omegaup-arena-runsubmit-popup
              v-show="currentPopupDisplayed === PopupDisplayed.RunSubmit"
              :preferred-language="problem.preferred_language"
              :languages="filteredLanguages"
              :next-submission-timestamp="
                problem.nextSubmissionTimestamp || new Date()
              "
              @dismiss="onPopupDismissed"
              @submit-run="onRunSubmitted"
            ></omegaup-arena-runsubmit-popup>
            <!-- TODO: Add the run details popup -->
          </template>
        </omegaup-overlay>
        <template v-if="problem.accepts_submissions">
          <omegaup-arena-ephemeral-grader
            v-if="!problem.karel_problem"
            :problem="problem"
            :can-submit="false"
            :accepted-languages="filteredLanguages"
          ></omegaup-arena-ephemeral-grader>
          <omegaup-arena-runs-v2
            :problem-alias="problem.alias"
            :runs="userRuns"
            :current-run-details="currentRunDetails"
            @show-run-details="
              (request) =>
                $emit('show-run-details', {
                  ...request,
                  isAdmin: user.admin,
                })
            "
          >
            <template #title>
              <div></div>
            </template>
            <template #runs>
              <div></div>
            </template>
          </omegaup-arena-runs-v2>
        </template>
      </b-tab>
      <b-tab v-if="user.admin" :title="T.wordsRuns" data-runs-tab>
        <omegaup-arena-runs
          :runs="allRuns"
          :show-all-runs="true"
          :show-problem="false"
          :show-details="true"
          :show-disqualify="true"
          :show-pager="true"
          :show-rejudge="true"
          :show-user="true"
          :problemset-problems="[]"
        >
          <template #title>
            <div></div>
          </template>
          <template #runs>
            <div></div>
          </template>
        </omegaup-arena-runs>
      </b-tab>
      <b-tab :title="T.wordsClarifications">a</b-tab>
    </b-tabs>
  </b-container>
</template>

<script lang="ts">
import { Vue, Component, Prop, Ref } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import * as ui from '../../ui';
import * as time from '../../time';

import arena_EphemeralGrader from '../arena/EphemeralGrader.vue';
import arena_RunSubmitPopup from '../arena/RunSubmitPopup.vue';
import arena_Runs from '../arena/Runs.vue';
import arena_Runsv2 from '../arena/Runsv2.vue';
import problem_SettingsSummary from './SettingsSummary.vue';
import omegaup_problemMarkdown from './ProblemMarkdown.vue';
import omegaup_Overlay from '../Overlay.vue';
import user_Username from '../user/Username.vue';

import { BootstrapVue } from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
Vue.use(BootstrapVue);

export interface Tab {
  name: string;
  text: string;
}

export enum PopupDisplayed {
  None,
  RunSubmit,
  RunDetails,
  Promotion,
  Demotion,
  Reviewer,
}

@Component({
  components: {
    'omegaup-arena-ephemeral-grader': arena_EphemeralGrader,
    'omegaup-arena-runsubmit-popup': arena_RunSubmitPopup,
    'omegaup-arena-runs': arena_Runs,
    'omegaup-arena-runs-v2': arena_Runsv2,
    'omegaup-markdown': omegaup_problemMarkdown,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-problem-settings-summary': problem_SettingsSummary,
    'omegaup-username': user_Username,
  },
})
export default class ProblemDetails extends Vue {
  @Prop() allRuns!: types.Run[];
  @Prop() currentRunDetails!: types.RunDetails | null;
  @Prop({ default: null }) languages!: null | string[];
  @Prop() problem!: types.ProblemDetails;
  @Prop() user!: types.UserInfoForProblem;
  @Prop() userRuns!: types.Run[];

  @Ref('statement-markdown')
  readonly statementMarkdown!: omegaup_problemMarkdown;

  T = T;
  ui = ui;
  time = time;

  PopupDisplayed = PopupDisplayed;
  currentPopupDisplayed = PopupDisplayed.None;

  get filteredLanguages(): string[] {
    if (!this.languages) {
      return this.problem.languages;
    }
    const languagesSet = new Set(this.languages);
    return this.problem.languages.filter((language) =>
      languagesSet.has(language),
    );
  }

  onRunSubmitted(code: string, language: string): void {
    this.$emit('submit-run', {
      code,
      language,
    });
    this.onPopupDismissed();
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.currentRunDetails = null;
    // TODO: Update the active tab.
  }

  onNewSubmission(): void {
    if (!this.user.loggedIn) {
      // TODO: Redirect to login
      return;
    }
    this.currentPopupDisplayed = PopupDisplayed.RunSubmit;
  }

  // TODO: handle onRunDetails

  onProblemRendered(): void {
    // TODO: We should probably refactor how the Markdown component is handled,
    // one for problems (with MathJax and the problem-specific logic) and another
    // for all other uses.
    //
    // It might be better for all of these things, plus the Markdown templating
    // system altogether to be replaced by the Markdown Vue component be able
    // to inject Vue components into the DOM after it's being rendered, so that
    // all the templating and interactivity can be handled by Vue instead of by
    // JavaScript.
    const libinteractiveInterfaceNameElement = this.statementMarkdown.$el.querySelector(
      'span.libinteractive-interface-name',
    ) as HTMLElement;
    if (
      libinteractiveInterfaceNameElement &&
      this.problem.settings?.interactive?.module_name
    ) {
      libinteractiveInterfaceNameElement.innerText = this.problem.settings.interactive.module_name.replace(
        /\.idl$/,
        '',
      );
    }

    const outputOnlyDownloadElement = this.statementMarkdown.$el.querySelector(
      '.output-only-download a',
    );
    if (outputOnlyDownloadElement) {
      outputOnlyDownloadElement.setAttribute(
        'href',
        `/probleminput/${this.problem.alias}/${this.problem.commit}/${this.problem.alias}-input.zip`,
      );
    }

    const libinteractiveDownloadFormElement = this.statementMarkdown.$el.querySelector(
      '.libinteractive-download form',
    ) as HTMLElement;
    if (libinteractiveDownloadFormElement) {
      libinteractiveDownloadFormElement.addEventListener(
        'submit',
        (e: Event) => {
          e.preventDefault();

          const form = e.target as HTMLElement;
          const alias = this.problem.alias;
          const commit = this.problem.commit;
          const os = (form.querySelector('.download-os') as HTMLInputElement)
            ?.value;
          const lang = (form.querySelector(
            '.download-lang',
          ) as HTMLInputElement)?.value;
          const extension = os == 'unix' ? '.tar.bz2' : '.zip';

          ui.navigateTo(
            `${window.location.protocol}//${
              window.location.host
            }/templates/${encodeURIComponent(alias)}/${encodeURIComponent(
              commit,
            )}/${encodeURIComponent(alias)}_${encodeURIComponent(
              os,
            )}_${encodeURIComponent(lang)}${encodeURIComponent(extension)}`,
          );
        },
      );
    }

    const libinteractiveDownloadLangElement = this.statementMarkdown.$el.querySelector(
      '.libinteractive-download .download-lang',
    ) as HTMLSelectElement;
    if (libinteractiveDownloadLangElement) {
      libinteractiveDownloadLangElement.addEventListener(
        'change',
        (e: Event) => {
          let form = e.target as HTMLElement;
          while (!form.classList.contains('libinteractive-download')) {
            if (!form.parentElement) {
              return;
            }
            form = form.parentElement;
          }
          (form.querySelector(
            '.libinteractive-extension',
          ) as HTMLElement).innerText = libinteractiveDownloadLangElement.value;
        },
      );
    }
  }
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';
</style>

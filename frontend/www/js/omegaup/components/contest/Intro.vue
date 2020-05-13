<template>
  <div class="card">
    <div class="card-body">
      <div class="text-center">
        <h2>{{ UI.contestTitle(contest) }}</h2>
        <div>
          <span>{{ contest.start_time.long() }}</span>
          <span>-</span>
          <span>{{ contest.finish_time.long() }}</span>
        </div>

        <template v-if="isLoggedIn">
          <!-- Wait for contest start -->
          <div v-if="now < contest.start_time.getTime()">
            <p>
              {{ T.contestWillBeginIn }}
              <span>
                <omegaup-countdown
                  v-bind:time-left="timeLeft"
                ></omegaup-countdown>
              </span>
            </p>
          </div>

          <div
            v-if="
              now > contest.finish_time.getTime() ||
                now > contest.start_time.getTime()
            "
          >
            <form
              v-on:submit.prevent="onStartContest"
              v-if="
                contest.admission_mode !== 'registration' ||
                  contest.user_registration_accepted
              "
            >
              <p
                v-if="
                  !needsBasicInformation && requestsUserInformation === 'no'
                "
              >
                {{ T.aboutToStart }}
              </p>
              <p
                v-if="needsBasicInformation"
                v-html="T.contestBasicInformationNeeded"
              ></p>
              <template v-if="requestsUserInformation !== 'no'">
                <p v-html="consentHtml"></p>
                <p>
                  <label>
                    <input
                      type="radio"
                      v-model="shareUserInformation"
                      v-bind:value="true"
                    />
                    {{ T.wordsYes }}
                  </label>
                  <label>
                    <input
                      type="radio"
                      v-model="shareUserInformation"
                      v-bind:value="false"
                    />
                    {{ T.wordsNo }}
                  </label>
                </p>
              </template>
              <button
                type="submit"
                v-bind:disabled="isButtonDisabled"
                class="btn btn-primary btn-lg start-contest-submit"
              >
                {{ T.startContest }}
              </button>
            </form>

            <!-- Must register -->
            <form v-else="" v-on:submit.prevent="onRequestAccess">
              <template v-if="!contest.user_registration_requested">
                <p>{{ T.mustRegisterToJoinContest }}</p>
                <button type="submit" class="btn btn-primary btn-lg">
                  {{ T.registerForContest }}
                </button>
              </template>
              <!-- Registration pending -->
              <div v-else-if="!contest.user_registration_answered">
                <p>{{ T.registrationPending }}</p>
              </div>
              <!-- Registration denied -->
              <div v-else-if="!contest.user_registration_answered === false">
                <p>{{ T.registrationDenied }}</p>
              </div>
            </form>
          </div>
        </template>

        <template v-else="">
          <!-- Must login to do anything -->
          <div class="panel">
            <p>{{ T.mustLoginToJoinContest }}</p>
            <a
              v-bind:href="`/login/?redirect=${requestURI}`"
              class="btn btn-primary"
              >{{ T.loginHeader }}</a
            >
          </div>
        </template>
      </div>
      <hr />
      <div>
        <h1>{{ T.registerForContestChallenges }}</h1>
        <p>{{ contest.description }}</p>
      </div>
      <div>
        <h1>{{ T.registerForContestRules }}</h1>
        <ul>
          <li v-if="contest.show_scoreboard_after">
            {{ T.contestNewFormScoreboardAtContestEnd }}
          </li>
          <li v-if="contest.window_length !== null">
            {{ differentStartsDescription }}
          </li>
          <li>{{ scoreboardDescription }}</li>
          <li>{{ submissionsGapDescription }}</li>
          <li>{{ penaltyTypes[contest.penalty_type] }}</li>
          <li v-if="contest.penalty !== 0">{{ penaltyDescription }}</li>
          <li>{{ feedbackTypes[contest.feedback] }}</li>
          <li v-if="contest.points_decay_factor !== 0">
            {{ pointsDecayDescription }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { omegaup } from '../../omegaup';
import T from '../../lang';
import * as markdown from '../../markdown';
import * as UI from '../../ui';
import omegaup_Countdown from '../Countdown.vue';

interface Statement {
  gitObjectId?: string;
  markdown?: string;
  statementType?: string;
}

@Component({
  components: {
    'omegaup-countdown': omegaup_Countdown,
  },
})
export default class ContestIntro extends Vue {
  @Prop() contest!: omegaup.Contest;
  @Prop() isLoggedIn!: boolean;
  @Prop() requestURI!: string;
  @Prop() requestsUserInformation!: string;
  @Prop() needsBasicInformation!: boolean;
  @Prop() statement!: Statement;

  T = T;
  UI = UI;
  penaltyTypes = {
    none: T.contestNewFormNoPenalty,
    problem_open: T.contestNewFormByProblem,
    contest_start: T.contestNewFormByContests,
    runtime: T.contestNewFormByRuntime,
  };
  feedbackTypes = {
    detailed: T.contestNewFormImmediateFeedbackDesc,
    none: '',
    summary: T.contestNewFormImmediateSummaryFeedbackDesc,
  };
  markdownConverter = markdown.markdownConverter();
  shareUserInformation = null;
  timePassed = 0;
  timerInterval = 0;
  now = Date.now();
  clock = '';

  get consentHtml(): string {
    if (!this.statement) {
      return '';
    }
    const markdown = this.statement.markdown || '';
    return this.markdownConverter.makeHtml(markdown);
  }

  get timeLeft() {
    if (this.contest && this.contest.start_time) {
      const timeLimit = this.contest.start_time.getTime() - Date.now();
      return timeLimit - this.timePassed;
    }
  }

  get differentStartsDescription(): string {
    return UI.formatString(T.contestIntroDifferentStarts, {
      window_length: this.formatTimeInRules(this.contest.window_length),
    });
  }

  get scoreboardDescription(): string {
    const contest = this.contest;
    if (!contest.scoreboard || !contest.finish_time || !contest.start_time) {
      return '';
    }
    if (contest.scoreboard === 100) {
      return T.contestIntroScoreboardTimePercentOneHundred;
    }
    if (contest.scoreboard === 0) {
      return T.contestIntroScoreboardTimePercentZero;
    }
    const minutesPercentage = Math.floor(
      (contest.scoreboard / 100) *
        ((contest.finish_time.getTime() - contest.start_time.getTime()) /
          60000),
    );
    return UI.formatString(T.contestIntroScoreboardTimePercent, {
      window_length: this.formatTimeInRules(minutesPercentage),
    });
  }

  get submissionsGapDescription(): string {
    if (!this.contest.submissions_gap) {
      return '';
    }
    return UI.formatString(T.contestIntroSubmissionsSeparationDesc, {
      window_length: this.formatTimeInRules(this.contest.submissions_gap / 60),
    });
  }

  get penaltyDescription(): string {
    return UI.formatString(T.contestIntroPenaltyDesc, {
      window_length: this.formatTimeInRules(this.contest.penalty),
    });
  }

  get pointsDecayDescription(): string {
    return UI.formatString(T.contestNewFormDecrementFactor, {
      window_length: this.contest.points_decay_factor,
    });
  }

  get isButtonDisabled(): boolean {
    return (
      this.needsBasicInformation ||
      (this.shareUserInformation === null &&
        this.requestsUserInformation !== 'no') ||
      (this.requestsUserInformation === 'required' &&
        !this.shareUserInformation)
    );
  }

  @Watch('timeLeft')
  onValueChanged(newValue: number): void {
    if (newValue <= 0) {
      if (!this.timerInterval) return;
      clearInterval(this.timerInterval);
      this.timerInterval = 0;
      this.now = Date.now();
    }
  }

  startTimer(): void {
    this.timerInterval = window.setInterval(() => (this.timePassed += 1), 1000);
  }

  formatTimeInRules(timeInMinutes?: number): string {
    if (!timeInMinutes) {
      return '';
    }
    const hours = Math.floor(timeInMinutes / 60);
    if (hours <= 0) {
      return timeInMinutes + 'm';
    } else {
      const minutes = timeInMinutes % 60;
      return `${hours}h${minutes}m`;
    }
  }

  onStartContest() {
    const request = {
      contest_alias: this.contest.alias,
      share_user_information: this.shareUserInformation,
    };
    let userInformationRequest = {};
    if (this.requestsUserInformation === 'required') {
      userInformationRequest = {
        privacy_git_object_id: this.statement.gitObjectId,
        statement_type: this.statement.statementType,
      };
    }
    $.extend(request, userInformationRequest);

    this.$emit('open-contest', request);
  }

  onRequestAccess() {
    this.$emit('request-access', this.contest.alias);
  }

  mounted() {
    this.startTimer();
  }
}
</script>

<template>
  <omegaup-overlay-popup @dismiss="onHide">
    <transition name="fade">
      <form data-demotion-popup class="h-auto w-auto">
        <template v-if="currentView === AvailableViews.Question">
          <div class="form-group">
            <div class="font-weight-bold pb-4">
              {{ T.reportProblemFormQuestion }}
            </div>
            <select
              v-model="selectedReason"
              class="control-label w-100"
              name="selectedReason"
            >
              <option value="no-problem-statement">
                {{ T.reportProblemFormNotAProblemStatement }}
              </option>
              <option value="poorly-described">
                {{ T.reportProblemFormPoorlyDescribed }}
              </option>
              <option value="offensive">
                {{ T.reportProblemFormOffensive }}
              </option>
              <option value="spam">
                {{ T.reportProblemFormSpam }}
              </option>
              <option value="duplicate">
                {{ T.reportProblemFormDuplicate }}
              </option>
              <option value="wrong-test-cases">
                {{ T.reportProblemFormCases }}
              </option>
              <option value="other">
                {{ T.reportProblemFormOtherReason }}
              </option>
            </select>
          </div>
          <div v-if="selectedReason == 'duplicate'" class="form-group">
            <label class="control-label w-100">{{
              T.reportProblemFormLinkToOriginalProblem
            }}</label>
            <input v-model="original" class="w-100" name="original" />
          </div>
          <div class="form-group">
            <label class="control-label w-100">{{
              T.reportProblemFormAdditionalComments
            }}</label>
            <textarea
              v-model="rationale"
              class="input-text w-100"
              name="rationale"
              type="text"
            ></textarea>
          </div>
          <div class="text-right">
            <button
              data-submit-report-button
              class="col-md-4 btn btn-primary"
              type="submit"
              :disabled="
                !selectedReason ||
                (!rationale && selectedReason == 'other') ||
                (!original && selectedReason == 'duplicate')
              "
              @click.prevent="onSubmit"
            >
              {{ T.wordsSend }}
            </button>
          </div>
        </template>
        <template v-if="currentView === AvailableViews.Thanks">
          <div class="w-100 h-100 h3 text-center">
            <h1>{{ T.reportProblemFormThanksForReview }}</h1>
          </div>
        </template>
      </form>
    </transition>
  </omegaup-overlay-popup>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import omegaup_OverlayPopup from '../OverlayPopup.vue';
import T from '../../lang';
import * as ui from '../../ui';

export enum AvailableViews {
  Content,
  Question,
  Thanks,
}

@Component({
  components: {
    'omegaup-overlay-popup': omegaup_OverlayPopup,
  },
})
export default class QualityNominationDemotionPopup extends Vue {
  AvailableViews = AvailableViews;
  T = T;
  ui = ui;
  rationale = '';
  original = '';
  currentView = AvailableViews.Question;
  selectedReason = '';

  onHide(): void {
    this.$emit('dismiss');
  }

  onSubmit(): void {
    this.$emit('submit', this);
    this.currentView = AvailableViews.Thanks;
    setTimeout(() => this.onHide(), 2000);
  }
}
</script>

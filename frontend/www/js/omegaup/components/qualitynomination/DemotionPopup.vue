<template>
  <omegaup-overlay-popup @dismiss="onHide">
    <transition name="fade">
      <form data-demotion-popup class="modal-form">
        <template v-if="currentView === AvailableViews.Question">
          <div class="modal-form__field">
            <div class="modal-form__title">
              {{ T.reportProblemFormQuestion }}
            </div>
            <select
              v-model="selectedReason"
              class="modal-form__control"
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
          <div v-if="selectedReason == 'duplicate'" class="modal-form__field">
            <label class="modal-form__label">{{
              T.reportProblemFormLinkToOriginalProblem
            }}</label>
            <input
              v-model="original"
              class="modal-form__control"
              name="original"
            />
          </div>
          <div class="modal-form__field">
            <label class="modal-form__label">{{
              T.reportProblemFormAdditionalComments
            }}</label>
            <textarea
              v-model="rationale"
              class="modal-form__control"
              name="rationale"
              type="text"
            ></textarea>
          </div>
          <div class="modal-form__actions">
            <button
              data-submit-report-button
              class="btn btn-primary"
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
          <div class="modal-form__thanks">
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

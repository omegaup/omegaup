<template>
  <div class="card">
    <h5 class="card-header">{{ T.wordsClarifications }}</h5>
    <slot name="new-clarification">
      <div class="card-body">
        <a
          href="#clarifications/all/new"
          class="btn btn-primary"
          @click="currentPopupDisplayed = PopupDisplayed.NewClarification"
        >
          {{ T.wordsNewClarification }}
        </a>
        <omegaup-overlay
          :show-overlay="currentPopupDisplayed !== PopupDisplayed.None"
          @hide-overlay="onPopupDismissed"
        >
          <template #popup>
            <omegaup-arena-new-clarification-popup
              v-show="currentPopupDisplayed === PopupDisplayed.NewClarification"
              :problems="problems"
              :users="users"
              :problem-alias="problemAlias"
              :username="username"
              @new-clarification="
                (contestClarification) =>
                  $emit('new-clarification', contestClarification)
              "
              @dismiss="onPopupDismissed"
            ></omegaup-arena-new-clarification-popup>
          </template>
        </omegaup-overlay>
      </div>
    </slot>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <slot name="table-title">
              <th class="text-center" scope="col">{{ T.wordsProblem }}</th>
            </slot>
            <th class="text-center" scope="col">{{ T.wordsAuthor }}</th>
            <th class="text-center" scope="col">{{ T.wordsTime }}</th>
            <th class="text-center" scope="col">{{ T.wordsMessage }}</th>
            <th class="text-center" scope="col">{{ T.wordsResult }}</th>
          </tr>
        </thead>
        <tbody>
          <omegaup-clarification
            v-for="clarification in clarifications"
            :key="clarification.clarification_id"
            :in-contest="inContest"
            :is-admin="isAdmin"
            :clarification="clarification"
            @clarification-response="
              (response) =>
                $emit('clarification-response', {
                  ...response,
                  message: clarification.message,
                })
            "
          ></omegaup-clarification>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

import arena_Clarification from './Clarification.vue';
import arena_NewClarification from './NewClarificationPopup.vue';
import omegaup_Overlay from '../Overlay.vue';

export enum PopupDisplayed {
  None,
  NewClarification,
}

@Component({
  components: {
    'omegaup-clarification': arena_Clarification,
    'omegaup-arena-new-clarification-popup': arena_NewClarification,
    'omegaup-overlay': omegaup_Overlay,
  },
})
export default class ArenaClarificationList extends Vue {
  @Prop() inContest!: boolean;
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop() clarifications!: types.Clarification[];
  @Prop({ default: () => [] }) problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop() problemAlias!: null | string;
  @Prop() username!: null | string;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;

  T = T;
  PopupDisplayed = PopupDisplayed;
  currentPopupDisplayed = this.popupDisplayed;

  onNewClarification(): void {
    this.currentPopupDisplayed = PopupDisplayed.NewClarification;
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.$emit('update:activeTab', 'clarifications');
  }

  @Watch('showNewClarificationPopup')
  onShowNewClarificationPopupChanged(newValue: boolean): void {
    if (!newValue) {
      this.currentPopupDisplayed = PopupDisplayed.None;
      return;
    }
    this.currentPopupDisplayed = PopupDisplayed.NewClarification;
    this.onNewClarification();
  }

  @Watch('popupDisplayed')
  onPopupDisplayedChanged(newValue: PopupDisplayed): void {
    this.currentPopupDisplayed = newValue;
    if (newValue === PopupDisplayed.None) return;
    if (newValue === PopupDisplayed.NewClarification) {
      this.onNewClarification();
    }
  }
}
</script>

<style lang="scss" scoped>
// Deep allows child components to inherit the styles (see: https://vue-loader.vuejs.org/guide/scoped-css.html#deep-selectors)
/deep/ pre {
  display: block;
  padding: 0.5rem;
  font-size: 0.8rem;
  line-height: 1.42857143;
  color: #333;
  word-break: break-all;
  background-color: #f5f5f5;
  border-radius: 4px;
}
</style>

<template>
  <div class="card" data-tab-clarifications>
    <h5 v-if="page && pageSize" class="card-header">
      {{
        ui.formatString(T.clarificationsRangeHeader, {
          lowCount: (page - 1) * pageSize + 1,
          highCount: page * pageSize,
        })
      }}
    </h5>
    <h5 v-else class="card-header">{{ T.wordsClarifications }}</h5>
    <div class="card-body">
      <slot name="new-clarification">
        <div v-if="problems.length" class="mb-3">
          <a
            href="#clarifications/all/new"
            data-new-clarification-button
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
                v-show="
                  currentPopupDisplayed === PopupDisplayed.NewClarification
                "
                :problems="problems"
                :users="users"
                :problem-alias="problemAlias"
                :username="username"
                :current-user-class-name="currentUserClassName"
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
      <div class="form-inline">
        <label v-if="allowFilterByAssignment">
          {{ T.wordsFilterByHomework }}
          <select
            v-model="selectedAssignment"
            class="form-control custom-select ml-1"
            name="problem"
          >
            <option
              v-for="assignmentName in assignmentsNames"
              :key="assignmentName"
              :value="assignmentName"
            >
              {{ assignmentName ? assignmentName : '' }}
            </option>
          </select>
        </label>
        <label :class="{ 'ml-md-4': allowFilterByAssignment }">
          {{ T.wordsFilterByProblem }}
          <select
            v-model="selectedProblem"
            class="form-control custom-select ml-1"
          >
            <option
              v-for="problemName in problemsNames"
              :key="problemName"
              :value="problemName"
            >
              {{ problemName }}
            </option>
          </select>
        </label>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr class="text-nowrap">
            <th class="text-center" scope="col">{{ T.clarificationInfo }}</th>
            <th class="text-center" scope="col">{{ T.wordsMessage }}</th>
            <th class="text-center" scope="col">{{ T.wordsResult }}</th>
          </tr>
        </thead>
        <tbody>
          <omegaup-clarification
            v-for="clarification in filteredClarifications"
            :key="clarification.clarification_id"
            :is-admin="isAdmin"
            :selected="clarificationSelected(clarification.clarification_id)"
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
    <div
      v-if="filteredClarifications.length === 0"
      class="empty-table-message py-2"
    >
      {{ T.clarificationsEmpty }}
    </div>
    <div v-if="pagerItems" class="card-footer">
      <omegaup-common-paginator
        :pager-items="pagerItems"
      ></omegaup-common-paginator>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';
import * as ui from '../../ui';

import arena_Clarification from './Clarification.vue';
import arena_NewClarification from './NewClarificationPopup.vue';
import omegaup_Overlay from '../Overlay.vue';
import clarificationsStore from '../../arena/clarificationsStore';
import common_Paginator from '../common/Paginator.vue';

export enum PopupDisplayed {
  None,
  NewClarification,
}

@Component({
  components: {
    'omegaup-clarification': arena_Clarification,
    'omegaup-arena-new-clarification-popup': arena_NewClarification,
    'omegaup-overlay': omegaup_Overlay,
    'omegaup-common-paginator': common_Paginator,
  },
})
export default class ArenaClarificationList extends Vue {
  @Prop({ default: false }) isAdmin!: boolean;
  @Prop() clarifications!: types.Clarification[];
  @Prop({ default: () => [] }) problems!: types.NavbarProblemsetProblem[];
  @Prop({ default: () => [] }) users!: types.ContestUser[];
  @Prop({ default: PopupDisplayed.None }) popupDisplayed!: PopupDisplayed;
  @Prop() problemAlias!: null | string;
  @Prop() username!: null | string;
  @Prop({ default: 'user-rank-unranked' }) currentUserClassName!: string;
  @Prop({ default: false }) showNewClarificationPopup!: boolean;
  @Prop({ default: false }) allowFilterByAssignment!: boolean;
  @Prop() pageSize!: number;
  @Prop() page!: number;
  @Prop() pagerItems!: types.PageItem[];

  T = T;
  ui = ui;
  PopupDisplayed = PopupDisplayed;
  currentPopupDisplayed = this.popupDisplayed;
  selectedAssignment: string | null = null;
  selectedProblem: string | null = null;

  onNewClarification(): void {
    this.currentPopupDisplayed = PopupDisplayed.NewClarification;
  }

  onPopupDismissed(): void {
    this.currentPopupDisplayed = PopupDisplayed.None;
    this.$emit('update:activeTab', 'clarifications');
  }

  clarificationSelected(clarificationId: number): boolean {
    return (
      clarificationsStore.state.selectedClarificationId === clarificationId
    );
  }

  get assignmentsNames(): Array<string | null> {
    return this.allowFilterByAssignment
      ? [
          ...new Set(
            this.clarifications.map(
              (clarification) => clarification.assignment_alias ?? null,
            ),
          ),
        ]
      : [];
  }

  get problemsNames(): string[] {
    return [
      ...new Set(
        this.clarifications.map((clarification) => clarification.problem_alias),
      ),
    ];
  }

  get filteredClarifications(): types.Clarification[] {
    return this.clarifications.filter(
      (clarification) =>
        (this.selectedAssignment === null ||
          clarification.assignment_alias === this.selectedAssignment) &&
        (this.selectedProblem === null ||
          clarification.problem_alias === this.selectedProblem),
    );
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
@import '../../../../sass/main.scss';
// >>> allows child components to inherit the styles (see: https://vue-loader.vuejs.org/guide/scoped-css.html#deep-selectors)
>>> pre {
  display: block;
  padding: 0.5rem;
  font-size: 0.8rem;
  line-height: 1.42857143;
  color: var(--clarifications-list-pre-font-color);
  word-break: break-all;
  background-color: var(--clarifications-list-pre-background-color);
  border-radius: 4px;
}

a {
  background-color: var(--btn-ok-background-color) !important;
  color: var(--btn-ok-font-color) !important;

  /* stylelint-disable-next-line no-descending-specificity */
  &:hover {
    color: var(--btn-ok-font-color) !important;
    text-decoration: none !important;
  }
}
</style>

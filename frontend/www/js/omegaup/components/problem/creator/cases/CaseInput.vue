<template>
  <div class="mt-3">
    <div class="form-group mb-4">
      <label>{{ T.problemCreatorCaseName }}</label>
      <input
        :value="caseName"
        data-problem-creator-case-input="name"
        name="case-name"
        class="form-control"
        required
        autocomplete="off"
        @input="onCaseNameInput"
      />
      <small class="form-text text-muted">{{
        T.problemCreatorCaseGroupNameHelper
      }}</small>
    </div>
    <div class="form-group">
      <label>{{ T.problemCreatorGroupName }}</label>
      <select v-model="caseGroup" name="case-group" class="custom-select">
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.text }}
        </option>
      </select>
    </div>

    <div v-show="!caseAutoPoints" class="form-group">
      <label>{{ T.problemCreatorPoints }}</label>
      <input
        :value="casePoints"
        name="case-points"
        class="form-control"
        type="number"
        min="0"
        @input="onCasePointsInput"
      />
    </div>
    <div class="form-group">
      <label>{{ T.problemCreatorAutomaticPointsRecommended }}</label>
      <small class="form-text text-muted d-block">{{
        T.problemCreatorAutomaticPointsHelperCase
      }}</small>
      <input
        type="checkbox"
        name="auto-points"
        :checked="caseAutoPoints"
        @change="toggleAutoPoints"
      />
    </div>
  </div>
</template>

<script lang="ts">
import { NIL } from 'uuid';
import { Component, Vue, Prop } from 'vue-property-decorator';
import { namespace } from 'vuex-class';
import T from '../../../../lang';

const casesStore = namespace('casesStore');

@Component
export default class CaseInput extends Vue {
  @Prop({ default: '' }) name!: string;
  @Prop({ default: NIL }) group!: string;
  @Prop({ default: 0 }) points!: number;
  @Prop({ default: true }) autoPoints!: boolean;
  @Prop({ default: false }) editMode!: boolean;

  // This return the group name, and the group ID of all groups in the store. Matching the required type for the select component./
  @casesStore.Getter('getGroupIdsAndNames') storedGroups!: {
    value: string;
    text: string;
  }[];

  caseName = this.name;
  caseGroup = this.group;
  casePoints: number = this.points;
  caseAutoPoints: boolean = this.autoPoints;

  T = T;

  toggleAutoPoints() {
    this.caseAutoPoints = !this.caseAutoPoints;
    if (this.caseAutoPoints) {
      this.casePoints = 0;
    }
  }

  // getGroupIdsAndNames getter is not instant, we need to wait for it to be defined otherwise the app will crash
  get options() {
    const noGroup = { value: NIL, text: T.problemCreatorNoGroup };
    if (!this.storedGroups) {
      return [noGroup];
    }
    if (
      this.editMode &&
      !this.storedGroups.find((group) => group.value === this.group)
    ) {
      this.caseGroup = NIL;
    }
    return [noGroup, ...this.storedGroups];
  }

  formatter(text: string) {
    return text.toLowerCase().replace(/[^a-zA-Z0-9_-]/g, '');
  }

  pointsFormatter(points: number) {
    return Math.max(points, 0);
  }

  onCaseNameInput(event: Event) {
    const input = event.target as HTMLInputElement;
    this.caseName = this.formatter(input.value);
    input.value = this.caseName;
  }

  onCasePointsInput(event: Event) {
    this.casePoints = this.pointsFormatter(
      Number((event.target as HTMLInputElement).value),
    );
  }
}
</script>

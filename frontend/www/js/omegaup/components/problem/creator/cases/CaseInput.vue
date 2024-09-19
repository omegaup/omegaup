<template>
  <div class="mt-3">
    <b-form-group
      :description="T.problemCreatorCaseGroupNameHelper"
      :label="T.problemCreatorCaseName"
      label-for="case-name"
      class="mb-4"
    >
      <b-form-input
        data-problem-creator-case-input="name"
        v-model="caseName"
        name="case-name"
        :formatter="formatter"
        required
        autocomplete="off"
      />
    </b-form-group>
    <b-form-group :label="T.problemCreatorGroupName" label-for="case-group">
      <b-form-select v-model="caseGroup" :options="options" name="case-group" />
    </b-form-group>

    <b-form-group
      v-show="casePoints !== null"
      :label="T.problemCreatorPoints"
      label-for="case-points"
    >
      <b-form-input
        v-model="casePoints"
        name="case-points"
        :formatter="pointsFormatter"
        type="number"
        number
        min="0"
        max="100"
      />
    </b-form-group>
    <b-form-group
      :label="T.problemCreatorAutomaticPointsRecommended"
      :description="T.problemCreatorAutomaticPointsHelperCase"
    >
      <b-form-checkbox
        :checked="casePoints === null"
        name="auto-points"
        @change="casePoints = casePoints === null ? 0 : null"
      >
      </b-form-checkbox>
    </b-form-group>
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
  @Prop({ default: null }) points!: number | null;
  @Prop({ default: false }) editMode!: boolean;

  // This return the group name, and the group ID of all groups in the store. Matching the required type for the select component./
  @casesStore.Getter('getGroupIdsAndNames') storedGroups!: {
    value: string;
    text: string;
  }[];

  caseName = this.name;
  caseGroup = this.group;
  casePoints: number | null = this.points;

  T = T;

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

  pointsFormatter(points: number | null) {
    if (points === null) {
      return null;
    }
    return Math.max(points, 0);
  }
}
</script>

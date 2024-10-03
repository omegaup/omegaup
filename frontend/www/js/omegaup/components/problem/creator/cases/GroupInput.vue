<template>
  <div class="mt-3">
    <b-form-group
      :description="T.problemCreatorCaseGroupNameHelper"
      :label="T.problemCreatorGroupName"
      label-for="case-name"
      class="mb-4"
    >
      <b-form-input
        v-model="groupName"
        data-problem-creator-group-input="name"
        :formatter="formatter"
        required
        autocomplete="off"
        name="group-name"
      />
    </b-form-group>
    <b-form-group
      v-show="groupPoints !== null"
      :label="T.problemCreatorPoints"
      label-for="case-points"
    >
      <b-form-input
        v-model="groupPoints"
        name="group-points"
        :formatter="pointsFormatter"
        type="number"
        number
        min="0"
        max="100"
      />
    </b-form-group>
    <b-form-group
      :label="T.problemCreatorAutomaticPoints"
      :description="T.problemCreatorAutomaticPointsHelperGroup"
    >
      <b-form-checkbox
        :checked="groupPoints === null"
        name="auto-points"
        @change="groupPoints = groupPoints === null ? 0 : null"
      >
      </b-form-checkbox>
    </b-form-group>
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import T from '../../../../lang';

@Component
export default class GroupInput extends Vue {
  @Prop({ default: '' }) name!: string;
  @Prop({ default: 0 }) points!: number | null;

  groupName = this.name;
  groupPoints: number | null = this.points;

  T = T;

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

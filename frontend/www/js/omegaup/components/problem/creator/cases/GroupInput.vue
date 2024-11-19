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
      v-show="!groupAutoPoints"
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
      />
    </b-form-group>
    <b-form-group
      :label="T.problemCreatorAutomaticPoints"
      :description="T.problemCreatorAutomaticPointsHelperGroup"
    >
      <b-form-checkbox
        :checked="groupAutoPoints"
        name="group-auto-points"
        @change="toggleGroupAutoPoints"
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
  @Prop({ default: 100 }) points!: number;
  @Prop({ default: true }) autoPoints!: boolean;

  groupName = this.name;
  groupPoints: number = this.points;
  groupAutoPoints: boolean = this.autoPoints;

  T = T;

  formatter(text: string) {
    return text.toLowerCase().replace(/[^a-zA-Z0-9_-]/g, '');
  }

  pointsFormatter(points: number) {
    return Math.max(points, 0);
  }

  toggleGroupAutoPoints() {
    this.groupAutoPoints = !this.groupAutoPoints;
    if (this.groupAutoPoints) {
      this.groupPoints = 100;
    }
  }
}
</script>

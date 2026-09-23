<template>
  <div class="mt-3">
    <div class="mb-3 mb-4">
      <label>{{ T.problemCreatorGroupName }}</label>
      <input
        :value="groupName"
        data-problem-creator-group-input="name"
        class="form-control"
        required
        autocomplete="off"
        name="group-name"
        @input="onGroupNameInput"
      />
      <small class="form-text text-muted">{{
        T.problemCreatorCaseGroupNameHelper
      }}</small>
    </div>
    <div v-show="!groupAutoPoints" class="mb-3">
      <label>{{ T.problemCreatorPoints }}</label>
      <input
        :value="groupPoints"
        name="group-points"
        class="form-control"
        type="number"
        min="0"
        @input="onGroupPointsInput"
      />
    </div>
    <div class="mb-3">
      <label>{{ T.problemCreatorAutomaticPoints }}</label>
      <small class="form-text text-muted d-block">{{
        T.problemCreatorAutomaticPointsHelperGroup
      }}</small>
      <input
        type="checkbox"
        name="group-auto-points"
        :checked="groupAutoPoints"
        @change="toggleGroupAutoPoints"
      />
    </div>
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

  onGroupNameInput(event: Event) {
    const input = event.target as HTMLInputElement;
    this.groupName = this.formatter(input.value);
    input.value = this.groupName;
  }

  onGroupPointsInput(event: Event) {
    this.groupPoints = this.pointsFormatter(
      Number((event.target as HTMLInputElement).value),
    );
  }
}
</script>

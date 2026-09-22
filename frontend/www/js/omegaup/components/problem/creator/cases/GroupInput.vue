<template>
  <div class="mt-3">
    <div class="form-group mb-4">
      <label for="group-name">{{ T.problemCreatorGroupName }}</label>
      <input
        id="group-name"
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
    <div v-show="!groupAutoPoints" class="form-group">
      <label for="group-points">{{ T.problemCreatorPoints }}</label>
      <input
        id="group-points"
        :value="groupPoints"
        name="group-points"
        class="form-control"
        type="number"
        min="0"
        @input="onGroupPointsInput"
      />
    </div>
    <div class="form-group">
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
    this.groupName = this.formatter((event.target as HTMLInputElement).value);
  }

  onGroupPointsInput(event: Event) {
    this.groupPoints = this.pointsFormatter(
      Number((event.target as HTMLInputElement).value),
    );
  }
}
</script>

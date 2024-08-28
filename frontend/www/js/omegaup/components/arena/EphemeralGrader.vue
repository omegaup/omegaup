<template>
  <div ref="ide-layout">
    <ephemeral-ide
      :accepted-languages="acceptedLanguages"
      :initial-language="initialLanguage"
      :problem="problem"
      :can-submit="canSubmit"
    />
  </div>
</template>

<script lang="ts">
import { Component, Vue, Prop, Ref } from 'vue-property-decorator';
import { types } from '../../api_types';
import Ephemeral from '../../graderv2/Ephemeral.vue';

@Component({
  components: {
    'ephemeral-ide': Ephemeral,
  },
})
export default class EphemeralGrader extends Vue {
  @Ref('ide-layout') readonly ideLayout!: HTMLDivElement;
  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: true }) canRun!: boolean;
  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: 'cpp17gcc' }) preferredLanguage!: string;

  loaded = false;

  get initialLanguage() {
    if (!this.acceptedLanguages.includes(this.preferredLanguage)) {
      return this.acceptedLanguages[0];
    }
    return this.preferredLanguage;
  }
}
</script>

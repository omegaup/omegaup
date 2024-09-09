<template>
  <ephemeral-ide
    :accepted-languages="acceptedLanguages"
    :initial-language="initialLanguage"
    :problem="problem"
    :can-submit="canSubmit"
    :can-run="canRun"
  >
    <template #zip-buttons>
      <slot name="zip-buttons"></slot>
    </template>
  </ephemeral-ide>
</template>

<script lang="ts">
import { Component, Vue, Prop } from 'vue-property-decorator';
import { types } from '../../api_types';
import Ephemeral from '../../graderv2/Ephemeral.vue';

@Component({
  components: {
    'ephemeral-ide': Ephemeral,
  },
})
export default class EphemeralGrader extends Vue {
  @Prop() problem!: types.ProblemInfo;
  @Prop({ default: false }) canSubmit!: boolean;
  @Prop({ default: true }) canRun!: boolean;
  @Prop({ default: () => [] }) acceptedLanguages!: string[];
  @Prop({ default: 'cpp17-gcc' }) preferredLanguage!: string;

  // note: initial source is for the IDE is also supported
  get initialLanguage() {
    if (!this.acceptedLanguages.includes(this.preferredLanguage)) {
      return this.acceptedLanguages[0];
    }
    return this.preferredLanguage;
  }
}
</script>

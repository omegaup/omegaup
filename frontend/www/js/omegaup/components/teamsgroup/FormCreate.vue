<template>
  <omegaup-teams-group-form
    :alias.sync="alias"
    :description.sync="description"
    :name.sync="name"
    :number-of-contestants.sync="numberOfContestants"
    :max-number-of-contestants="maxNumberOfContestants"
    @submit="(request) => $emit('create-teams-group', { ...request, alias })"
  >
    <template #teams-group-title>
      <div class="card-header">
        <h3 class="card-title">
          {{ T.omegaupTitleTeamsGroupNew }}
        </h3>
      </div>
    </template>
  </omegaup-teams-group-form>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import teamsgroup_FormBase from './FormBase.vue';
import T from '../../lang';
import { generateAlias } from '../../alias';

export default defineComponent({
  name: 'TeamsGroupFormCreate',
  components: {
    'omegaup-teams-group-form': teamsgroup_FormBase,
  },
  props: {
    maxNumberOfContestants: {
      type: Number,
      default: 10,
    },
  },
  emits: ['validate-unused-alias', 'create-teams-group'],
  data() {
    return {
      T,
      alias: null as null | string,
      description: null as null | string,
      name: null as null | string,
      numberOfContestants: 3,
    };
  },
  watch: {
    alias(newValue: string): void {
      this.$emit('validate-unused-alias', newValue);
    },
    name(newValue: string): void {
      this.alias = generateAlias(newValue);
    },
  },
});
</script>

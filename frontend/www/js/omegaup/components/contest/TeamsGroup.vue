<template>
  <div class="card mt-3">
    <div class="card-body">
      <form class="form" @submit.prevent="onSubmit">
        <div class="form-group">
          <label>{{ T.wordsGroup }}</label>
          <omegaup-common-typeahead
            :existing-options="searchResultTeamsGroups"
            :value.sync="typeaheadGroup"
            :disabled="hasSubmissions"
            @update-existing-options="
              (query) => $emit('update-search-result-teams-groups', query)
            "
          >
          </omegaup-common-typeahead>
        </div>
        <button
          class="btn btn-primary"
          type="submit"
          :disabled="hasSubmissions"
        >
          {{ T.contestEditTeamsGroupReplace }}
        </button>
      </form>
    </div>
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th class="text-center">
            {{ T.contestEditRegisteredGroupAdminName }}
          </th>
        </tr>
      </thead>
      <tbody>
        <tr :key="teamsGroup.alias">
          <td>
            <a :href="`/teamsgroup/${teamsGroup.alias}/edit/#edit`">
              <omegaup-markdown :markdown="teamsGroup.name"></omegaup-markdown>
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import { types } from '../../api_types';
import T from '../../lang';
import common_Typeahead from '../common/Typeahead.vue';
import omegaup_Markdown from '../Markdown.vue';

@Component({
  components: {
    'omegaup-common-typeahead': common_Typeahead,
    'omegaup-markdown': omegaup_Markdown,
  },
})
export default class TeamsGroup extends Vue {
  @Prop() teamsGroup!: types.ContestGroup;
  @Prop() searchResultTeamsGroups!: types.ListItem[];
  @Prop({ default: false }) hasSubmissions!: boolean;

  T = T;
  typeaheadGroup: null | types.ListItem = null;

  onSubmit(): void {
    const name = this.searchResultTeamsGroups.find(
      (teamsGroup) => teamsGroup.key === this.typeaheadGroup?.key,
    )?.value;
    this.$emit('replace-teams-group', {
      alias: this.typeaheadGroup?.key,
      name,
    });
  }

  @Watch('teamsGroup')
  onTeamsGroupChange(): void {
    this.typeaheadGroup = null;
  }
}
</script>

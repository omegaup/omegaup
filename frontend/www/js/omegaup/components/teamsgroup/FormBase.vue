<template>
  <div class="card">
    <slot name="teams-group-title"></slot>
    <div class="card-body">
      <form
        class="needs-validation"
        data-teams-group
        @submit.prevent="$emit('submit', { name, description })"
      >
        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.wordsName }}
              <input
                v-model="name"
                name="title"
                required
                type="text"
                class="form-control"
              />
            </label>
          </div>

          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.contestNewFormShortTitleAlias }}
              <input
                v-model="teamsGroupAlias"
                name="alias"
                required
                type="text"
                class="form-control"
                disabled="true"
              />
            </label>
            <p class="help-block">{{ T.contestNewFormShortTitleAliasDesc }}</p>
          </div>
        </div>

        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.teamsGroupNewFormDescription }}
              <textarea
                v-model="description"
                required
                name="description"
                cols="30"
                rows="5"
                class="form-control"
              ></textarea>
            </label>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary">
            <slot name="teams-group-submit-button">
              {{ T.teamsGroupFormCreate }}
            </slot>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch, Emit } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class TeamsGroupFormBase extends Vue {
  @Prop() teamsGroupAlias!: null | string;
  @Prop() teamsGroupDescription!: null | string;
  @Prop() teamsGroupName!: null | string;

  T = T;
  alias: null | string = this.teamsGroupAlias;
  description: null | string = this.teamsGroupDescription;
  name: null | string = this.teamsGroupName;

  @Watch('alias')
  @Emit('update:teamsGroupAlias')
  onAliasUpdated(newValue: string): string {
    return newValue;
  }

  @Watch('description')
  @Emit('update:teamsGroupDescription')
  onDescriptionUpdated(newValue: string): string {
    return newValue;
  }

  @Watch('name')
  @Emit('update:teamsGroupName')
  onNameUpdated(newValue: string): string {
    return newValue;
  }
}
</script>

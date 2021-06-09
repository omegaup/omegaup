<template>
  <div class="card">
    <div v-if="!isUpdate" class="card-header">
      <h3 class="card-title">
        {{ T.omegaupTitleTeamsGroupNew }}
      </h3>
    </div>
    <div class="card-body">
      <form class="needs-validation" data-group-new @submit.prevent="onSubmit">
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
                v-model="alias"
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
          <button v-if="isUpdate" type="submit" class="btn btn-primary">
            {{ T.teamsGroupFormUpdate }}
          </button>
          <button v-else type="submit" class="btn btn-primary">
            {{ T.teamsGroupFormCreate }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from 'vue-property-decorator';
import T from '../../lang';
import latinize from 'latinize';

@Component
export default class TeamsGroupForm extends Vue {
  @Prop({ default: null }) teamsGroupAlias!: null | string;
  @Prop({ default: null }) teamsGroupDescription!: null | string;
  @Prop({ default: null }) teamsGroupName!: null | string;
  @Prop({ default: false }) isUpdate!: boolean;

  T = T;
  alias: null | string = this.teamsGroupAlias;
  description: null | string = this.teamsGroupDescription;
  name: null | string = this.teamsGroupName;

  onSubmit(): void {
    if (this.isUpdate) {
      this.$emit('update-teams-group', {
        name: this.name,
        description: this.description,
      });
      return;
    }
    this.$emit('create-teams-group', {
      name: this.name,
      alias: this.alias,
      description: this.description,
    });
  }

  generateAlias(name: string): string {
    // Remove accents
    let generatedAlias = latinize(name);

    // Replace whitespace
    generatedAlias = generatedAlias.replace(/\s+/g, '-');

    // Remove invalid characters
    generatedAlias = generatedAlias.replace(/[^a-zA-Z0-9_-]/g, '');

    generatedAlias = generatedAlias.substring(0, 32);

    return generatedAlias;
  }

  @Watch('alias')
  onAliasChanged(newValue: string): void {
    if (this.isUpdate) {
      return;
    }
    this.$emit('validate-unused-alias', newValue);
  }

  @Watch('name')
  onNameChanged(newValue: string): void {
    if (this.isUpdate) {
      return;
    }
    this.alias = this.generateAlias(newValue);
  }
}
</script>

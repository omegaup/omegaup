<template>
  <div class="card">
    <div v-if="!isUpdate" class="card-header">
      <h3 class="card-title">
        {{ T.omegaupTitleGroupsNew }}
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
              {{ T.groupNewFormDescription }}
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
            {{ T.groupNewFormUpdateGroup }}
          </button>
          <button v-else type="submit" class="btn btn-primary">
            {{ T.groupNewFormCreateGroup }}
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
export default class GroupForm extends Vue {
  @Prop({ default: '' }) groupAlias!: string;
  @Prop({ default: '' }) groupDescription!: string;
  @Prop({ default: '' }) groupName!: string;
  @Prop({ default: false }) isUpdate!: boolean;

  T = T;
  alias: string = this.groupAlias;
  description: string = this.groupDescription;
  name: string = this.groupName;

  onSubmit(): void {
    if (this.isUpdate) {
      this.$emit('update-group', this.name, this.description);
      return;
    }
    this.$emit('create-group', this.name, this.alias, this.description);
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

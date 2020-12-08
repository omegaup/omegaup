<template>
  <div class="card">
    <div v-if="!isUpdate" class="card-header">
      <h3 class="card-title">
        {{ T.omegaupTitleGroupsNew }}
      </h3>
    </div>
    <div class="card-body">
      <form class="needs-validation" data-group-new>
        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.wordsName }}
              <input
                v-model="name"
                required
                type="text"
                class="form-control"
                @blur="onGenerateAlias"
              />
            </label>
          </div>

          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.contestNewFormShortTitleAlias }}
              <input
                v-model="alias"
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
                cols="30"
                rows="5"
                class="form-control"
              ></textarea>
            </label>
          </div>
        </div>

        <div class="form-group">
          <button
            v-if="isUpdate"
            type="submit"
            class="btn btn-primary"
            @click.prevent="$emit('update-group', name, alias, description)"
          >
            {{ T.groupNewFormUpdateGroup }}
          </button>
          <button
            v-else
            type="submit"
            class="btn btn-primary"
            @click.prevent="$emit('create-group', name, alias, description)"
          >
            {{ T.groupNewFormCreateGroup }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import latinize from 'latinize';

@Component
export default class GroupForm extends Vue {
  @Prop({ default: false }) isUpdate!: boolean;

  T = T;
  alias: string = '';
  description: string = '';
  name: string = '';

  onGenerateAlias(): void {
    if (this.isUpdate) {
      return;
    }

    // Remove accents
    let generatedAlias = latinize(this.name);

    // Replace whitespace
    generatedAlias = generatedAlias.replace(/\s+/g, '-');

    // Remove invalid characters
    generatedAlias = generatedAlias.replace(/[^a-zA-Z0-9_-]/g, '');

    generatedAlias = generatedAlias.substring(0, 32);

    this.alias = generatedAlias;
  }
}
</script>

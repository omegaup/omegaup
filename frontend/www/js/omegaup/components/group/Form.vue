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
import { defineComponent } from 'vue';
import T from '../../lang';
import { generateAlias } from '../../alias';

export default defineComponent({
  name: 'GroupForm',
  props: {
    groupAlias: {
      type: String,
      default: '',
    },
    groupDescription: {
      type: String,
      default: '',
    },
    groupName: {
      type: String,
      default: '',
    },
    isUpdate: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update-group', 'create-group', 'validate-unused-alias'],
  data() {
    return {
      T,
      alias: this.groupAlias,
      description: this.groupDescription,
      name: this.groupName,
    };
  },
  watch: {
    alias(newValue: string): void {
      if (this.isUpdate) {
        return;
      }
      this.$emit('validate-unused-alias', newValue);
    },
    name(newValue: string): void {
      if (this.isUpdate) {
        return;
      }
      this.alias = generateAlias(newValue);
    },
  },
  methods: {
    onSubmit(): void {
      if (this.isUpdate) {
        this.$emit('update-group', this.name, this.description);
        return;
      }
      this.$emit('create-group', this.name, this.alias, this.description);
    },
  },
});
</script>

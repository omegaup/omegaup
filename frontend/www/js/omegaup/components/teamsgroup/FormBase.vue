<template>
  <div class="card">
    <slot name="teams-group-title"></slot>
    <div class="card-body">
      <form
        class="needs-validation"
        data-teams-group
        @submit.prevent="
          $emit('submit', {
            name: currentName,
            description: currentDescription,
            numberOfContestants: currentNumberOfContestants,
          })
        "
      >
        <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.wordsName }}
              <input
                v-model="currentName"
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
                v-model="currentDescription"
                required
                name="description"
                cols="30"
                rows="5"
                class="form-control"
              ></textarea>
            </label>
          </div>

          <div class="form-group col-md-6">
            <label class="control-label w-100">
              {{ T.contestNewFormNumberOfContestants }}
              <input
                v-model="currentNumberOfContestants"
                name="number-of-contestants"
                required
                :max="maxNumberOfContestants"
                :min="minNumberOfContestants"
                type="number"
                class="form-control"
              />
            </label>
            <p class="help-block">
              {{ T.contestNewFormNumberOfContestantsDesc }}
            </p>
          </div>
        </div>

        <div class="form-group">
          <button type="submit" class="btn btn-primary" data-create-teams-group>
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
  @Prop() alias!: null | string;
  @Prop() description!: null | string;
  @Prop() name!: null | string;
  @Prop({ default: 3 }) numberOfContestants!: number;
  @Prop({ default: 10 }) maxNumberOfContestants!: number;
  @Prop({ default: 1 }) minNumberOfContestants!: number;

  T = T;
  currentAlias: null | string = this.alias;
  currentDescription: null | string = this.description;
  currentName: null | string = this.name;
  currentNumberOfContestants: number = this.numberOfContestants;

  @Watch('currentAlias')
  @Emit('update:alias')
  onAliasUpdated(newValue: string): string {
    return newValue;
  }

  @Watch('currentDescription')
  @Emit('update:description')
  onDescriptionUpdated(newValue: string): string {
    return newValue;
  }

  @Watch('currentName')
  @Emit('update:name')
  onNameUpdated(newValue: string): string {
    return newValue;
  }

  @Watch('currentNumberOfContestants')
  @Emit('update:numberOfContestants')
  onNumberOfContestantsUpdated(newValue: number): number {
    return newValue;
  }
}
</script>

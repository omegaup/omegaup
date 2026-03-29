<template>
  <div class="card">
    <div class="card-header">
      <h2 class="card-title">{{ T.omegaupTitleHelp }}</h2>
    </div>
    <div class="card-body">
      <p class="lead">{{ T.helpWelcomeMessage }}</p>
      <div class="row mt-4">
        <div
          v-for="resource in helpResources"
          :key="resource.name"
          class="col-md-6 col-lg-4 mb-4"
        >
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h5 class="card-title">
                <span v-if="resource.icon" class="mr-2">{{
                  resource.icon
                }}</span>
                {{ T[`helpResource${capitalizeFirst(resource.name)}`] }}
              </h5>
              <p class="card-text">
                {{
                  T[`helpResource${capitalizeFirst(resource.name)}Description`]
                }}
              </p>
              <a
                :href="resource.url"
                :target="resource.external ? '_blank' : '_self'"
                :rel="resource.external ? 'noopener noreferrer' : ''"
                class="btn btn-primary btn-sm"
              >
                {{ T.helpVisit }}
                <span v-if="resource.external"> ↗</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import { types } from '../../api_types';

@Component
export default class CommonHelp extends Vue {
  @Prop() helpResources!: types.HelpResource[];

  T = T;

  capitalizeFirst(str: string): string {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
}
</script>

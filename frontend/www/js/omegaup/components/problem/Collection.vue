<template>
  <div>
    <div class="row mb-2">
      <div class="col-md-7">
        <h1 class="card-title">{{ T.collectionTitle }}</h1>
      </div>
      <div class="col-md-5 text-right align-self-end">
        <a href="/problem/" data-nav-problems-all>{{ T.navAllProblems }}</a>
      </div>
    </div>
    <div class="card panel panel-default">
      <div class="card-header panel-heading">
        <h5 class="card-title panel-title">
          {{ T.problemCollectionEducationLevel }}
        </h5>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row"></div>
        </div>
      </div>
    </div>
    <div class="card panel panel-default">
      <div class="card-header panel-heading">
        <h5 class="card-title panel-title">
          {{ T.problemCollectionOthers }}
        </h5>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import T from '../../lang';

@Component
export default class Collection extends Vue {
  T = T;
}
</script>

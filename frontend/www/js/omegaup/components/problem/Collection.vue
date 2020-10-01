<template>
  <div>
    <div class="card">
      <div class="card-body">
        <h5 class="card-header">{{ T.collectionTitle }}</h5>
      </div>
    </div>
    <div class="card panel panel-default">
      <div class="card-header panel-heading">
        <h4 class="card-title panel-title">
          {{problemCollectionEducationLevel}}
        </h4>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row">
            
          </div>
        </div>
      </div>
    </div>
    <div class="card panel panel-default">
      <div class="card-header panel-heading">
        <h4 class="card-title panel-title">
          {{problemCollectionOthers}}
        </h4>
      </div>
      <div class="card-body panel-body">
        <div class="container-fluid">
          <div class="row">
            
          </div>
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

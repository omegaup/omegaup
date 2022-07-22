<template>
  <div class="test">
    <golden-layout>
      <gl-row>
        <gl-component title="component1">
          <h1>Component 1</h1>
        </gl-component>
        <gl-stack>
          <gl-component title="component2">
            <h1>Component 2</h1>
          </gl-component>
          <gl-component title="component3">
            <h1>Component 3</h1>
          </gl-component>
        </gl-stack>
      </gl-row>
    </golden-layout>
  </div>
</template>

<script lang="ts">
import { Vue, Component } from 'vue-property-decorator';
import vgl, {
  glComponent,
  glRow,
  glStack,
  goldenLayout,
} from 'vue-golden-layout';
import 'golden-layout/src/css/goldenlayout-light-theme.css';

Vue.use(vgl);

@Component({
  components: {
    glComponent,
    goldenLayout,
    glRow,
    glStack,
  },
})
export default class GraderEphemeral extends Vue {}
</script>

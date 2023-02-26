<template>
  <!-- id-lint off -->
  <div id="layoutContainer"></div>
  <!-- id-lint on -->
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import * as GoldenLayout from 'golden-layout';
import CaseSelectorComponent from './CaseSelectorComponent.vue';
import MonacoDiffComponent from './MonacoDiffComponent.vue';
import MonacoEditorComponent from './MonacoEditorComponent.vue';
import SettingsComponent from './SettingsComponent.vue';
import TextEditorComponent from './TextEditorComponent.vue';
import ZipViewerComponent from './ZipViewerComponent.vue';

@Component({
  components: {},
})
export default class GraderEphemeral extends Vue {
  @Prop() goldenLayoutSettings!: any;
  mounted() {
    const layout = new GoldenLayout(
      this.goldenLayoutSettings,
      document.getElementById('layoutContainer'),
    );

    let componentMapping = {};
    this.registerVueComponent(
      layout,
      'case-selector-component',
      CaseSelectorComponent,
      componentMapping,
    );
    this.registerVueComponent(
      layout,
      'monaco-editor-component',
      MonacoEditorComponent,
      componentMapping,
    );
    this.registerVueComponent(
      layout,
      'monaco-diff-component',
      MonacoDiffComponent,
      componentMapping,
    );
    this.registerVueComponent(
      layout,
      'settings-component',
      SettingsComponent,
      componentMapping,
    );
    this.registerVueComponent(
      layout,
      'text-editor-component',
      TextEditorComponent,
      componentMapping,
    );
    this.registerVueComponent(
      layout,
      'zip-viewer-component',
      ZipViewerComponent,
      componentMapping,
    );
  }

  registerVueComponent(
    layout: any,
    componentName: string,
    component: Vue,
    componentMap: any,
  ) {
    layout.registerComponent(
      componentName,
      function (container: any, componentState: any) {
        container.on('open', () => {
          let vueComponents: { [key: string]: Vue } = {};
          vueComponents[componentName] = component;
          let props: { [key: string]: { store: any; storeMapping: any } } = {
            //store,
            storeMapping: componentState.storeMapping,
          };
          for (let k in componentState) {
            if (k == 'id') continue;
            if (!Object.prototype.hasOwnProperty.call(componentState, k))
              continue;
            props[k] = componentState[k];
          }
          let vue = new Vue({
            el: container.getElement()[0],
            components: vueComponents,
            render: function (createElement) {
              return createElement(componentName, {
                props: props,
              });
            },
          });
          let vueComponent = vue.$children[0];
          if (vueComponent.title) {
            container.setTitle(vueComponent.title);
            vueComponent.$watch('title', function (title) {
              container.setTitle(title);
            });
          }
          if (vueComponent.onResize) {
            container.on('resize', () => vueComponent.onResize());
          }
          componentMap[componentState.id] = vueComponent;
        });
      },
    );
  }
}
</script>

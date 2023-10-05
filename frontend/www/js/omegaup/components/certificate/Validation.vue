<template>
  <div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      {{ T.certificateValidationTitle }}
    </h5>
    <div class="card-body">
      <div v-if="isValid">
        <p class="title">
          {{
            ui.formatString(T.certificateValidationEnteredCode, {
              code: verificationCode,
            })
          }}
        </p>
        <p class="description">{{ T.certificateValidationCertifyValidity }}</p>
        <iframe :src="certificate" width="100%" height="600px"></iframe>
      </div>
      <div v-else>
        <p class="title">
          {{
            ui.formatString(T.certificateValidationEnteredCode, {
              code: verificationCode,
            })
          }}
        </p>
        <p class="title">{{ T.certificateValidationStatus }}<span class="title-invalid">{{ T.certificateValidationInvalid }}</span></p>
        <p class="description">
          {{
            ui.formatString(T.certificateValidationNotFound, {
              code: verificationCode,
            })
          }}
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop } from 'vue-property-decorator';
import T from '../../lang';
import * as ui from '../../ui';

@Component
export default class Validation extends Vue {
  @Prop() verificationCode!: string;
  @Prop() isValid!: boolean;
  @Prop() certificate?: string;
  
  T = T;
  ui = ui;
}
</script>

<style lang="scss" scoped>
@import '../../../../sass/main.scss';

p.title {
  font-size: 1.24rem;
}

span.title-invalid {
  font-size: 1.24rem;
  color: red;
}

p.description {
  font-size: 1rem;
}
</style>

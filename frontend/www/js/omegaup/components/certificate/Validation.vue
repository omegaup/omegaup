<template>
  <div class="card">
    <h5 class="card-header d-flex justify-content-between align-items-center">
      {{ T.certificateValidationTitle }}
    </h5>
    <div class="card-body">
      <div v-if="isValid">
        <p class="title">
          {{ T.certificateValidationEnteredCode }}
          <code class="title">{{ verificationCode }}</code>
        </p>
        <p class="description">{{ T.certificateValidationCertifyValidity }}</p>
        <div class="embed-responsive embed-responsive-4by3">
          <object
            :data="certificateUrl"
            type="application/pdf"
            class="embed-responsive-item"
          ></object>
        </div>
      </div>
      <div v-else>
        <p class="title">
          {{ T.certificateValidationEnteredCode }}
          <code class="title">{{ verificationCode }}</code>
        </p>
        <p class="title">
          {{ T.certificateValidationStatus }}
          <span class="title-invalid">
            {{ T.certificateValidationInvalid.toUpperCase() }}
          </span>
        </p>
        <p class="description">
          {{ T.certificateValidationNotFound1 }}
          <code class="description">{{ verificationCode }}</code>
          {{ T.certificateValidationNotFound2 }}
        </p>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../../lang';

// PoC: vue-property-decorator → defineComponent() (works on Vue 2.7 and Vue 3).
export default defineComponent({
  name: 'CertificateValidation',
  props: {
    verificationCode: {
      type: String,
      required: true,
    },
    isValid: {
      type: Boolean,
      required: true,
    },
    certificate: {
      type: String,
      default: undefined,
    },
  },
  data() {
    return {
      T,
      certificateUrl: null as string | null,
    };
  },
  created() {
    if (this.certificate === undefined) {
      return;
    }
    const decodedData = atob(this.certificate);
    const unicode = new Array(decodedData.length);
    for (let i = 0; i < decodedData.length; i++) {
      unicode[i] = decodedData.charCodeAt(i);
    }
    const byteArray = new Uint8Array(unicode);
    const blob = new Blob([byteArray], {
      type: 'application/pdf',
    });
    this.certificateUrl = window.URL.createObjectURL(blob);
  },
});
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

code.title {
  color: black;
  font-size: 1.24rem;
  background-color: rgba(222, 222, 222, 0.4);
  padding: 0.25rem 0.5rem;
}

code.description {
  color: black;
  font-size: 1rem;
  background-color: rgba(222, 222, 222, 0.4);
  padding: 0.25rem 0.5rem;
}
</style>

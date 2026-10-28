<template>
  <div class="container-fluid px-3 px-md-5 py-4 py-md-5">
    <div class="card mx-auto">
      <h5 class="card-header h2-md">{{ T.passwordResetRequestTitle }}</h5>
      <form class="card-body">
        <div class="form-group">
          <h5 class="card-title">{{ T.profileEmail }}</h5>
          <input
            v-model="email"
            type="text"
            name="email"
            class="form-control"
          />
        </div>
        <button
          class="btn btn-primary form-control"
          @click.prevent="$emit('forgot-password', email)"
        >
          {{ T.wordsSend }}
        </button>
      </form>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import T from '../../lang';

export default defineComponent({
  name: 'PasswordRecover',
  data() {
    return {
      T,
      email: '',
    };
  },
});
</script>

<style scoped>
.card {
  max-width: 500px;
}

.card-header {
  font-size: 1.5rem;
}

@media (min-width: 768px) {
  .card-header {
    font-size: 1.5rem;
  }
}
</style>

/**
 * Pinia bootstrap for the grader bundle. Import this module once before GraderStore
 * so setActivePinia is set for useGraderStore() outside Vue components.
 */
import Vue from 'vue';
import { PiniaVuePlugin, createPinia, setActivePinia } from 'pinia';

Vue.use(PiniaVuePlugin);

export const graderPinia = createPinia();
setActivePinia(graderPinia);

<template>
    <input class="typeahead form-control" ref="input" @change="updateInput()" :value="value">
</template>
<script>
import {UI} from '../omegaup.js';
export default {
    data: function() {
        return {
            value: ""
        }
    },
    mounted: function() {
        UI.userTypeahead($(this.$el));
    },
    methods: {
        updateInput() {
            this.$emit('input', this.$refs.input.value);
        }
    }
}
</script>

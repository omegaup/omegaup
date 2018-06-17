<template>
    <input class="typeahead form-control">
</template>
<script>
import {UI} from '../omegaup.js';
export default {
    props: {
        problems: Array,
        placeholder: String,
    },
    mounted: function() {
        var self = this;
        console.log(self.problems);
        $(self.$el).typeahead(
            {
                minLength: 3,
                highlight: false
            },
            {
                source: self.problems,
                async: true,
                display: 'alias',
                templates: {
                    suggestion: function(val) {
                        console.log(val);
                        //return UI.formatString('<strong>%(title)</strong> (%(alias))');
                    }
                }
            }
        )
        .on('typeahead:select', function(a) {
            console.log(a);
        })
        .on('typeahead:autocomplete', function(a) {
            console.log(a)
        });
    }
}
</script>

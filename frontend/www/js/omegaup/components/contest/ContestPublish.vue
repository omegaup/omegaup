<template>
<div class="panel panel-primary">
    <div class='panel-body'>
        <form class='contest-publish-form'>
            <div class="form-group">
                <label for="public">{#contestNewFormPublic#}</label>
                <select name='public' id='public' class="form-control">
                    <option value='0' selected="selected">{#wordsNo#}</option>
                    <option value='1'>{#wordsYes#}</option>
                </select>
                <p class="help-block">{#contestNewFormPublicDesc#}</p>
            </div>

            <button class="btn btn-primary" type="submit">{#wordsSaveChanges#}</button>
        </form>
    </div>
</div>
</template>
<script>
import {T} from '../../omegaup.js';
export default {
    data: function() {
        return {T: T}
    }
}
</script>

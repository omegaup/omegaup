<form id="clarification" method="POST">
  <div class="close-container">
    <button class="close">&times;</button>
  </div>
  <label>{#wordsProblem#} <select name="problem"></select></label>
  {if $admin}
  <label>{#wordsMessageTo#} <select name="user"></select></label>
  {/if}
  <br/>
  <label>{#arenaClarificationCreateMaxLength#}
    <textarea name="message" maxlength="200" style="width: 100%; resize: none;" rows="10"></textarea>
  </label><br/>
  <input type="submit" />
</form>


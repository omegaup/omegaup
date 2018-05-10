<form id="clarification" method="POST">
  <button class="close">&times;</button>
  <label>{#wordsProblem#} <select name="problem"></select></label>
  {if $admin}
  <label>{#wordsMessageTo#} <select name="user"></select></label>
  {/if}
  <br/>
  <label>{#arenaClarificationCreateMaxLength#}
    <textarea name="message" maxlength="200"></textarea>
  </label><br/>
  <input type="submit" />
</form>


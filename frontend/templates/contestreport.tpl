{foreach name=outer item=contestantData from=$contestReport}
  <hr />
  {foreach key=key item=item from=$contestReport}
    {$key}: {$item}<br />
  {/foreach}
{/foreach}
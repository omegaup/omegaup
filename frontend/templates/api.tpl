{include file='head.tpl'}

<div style="width: 920px; position: relative; margin: 0 auto 0 auto; ">
    <table>
    <tr>
        <td>
            <div class="post footer" style="width: 130px; min-height: 300px;">
                <div class="copy" >
                <a href='/api/explorer/sesion/'>Sesion</a>
                <a href='/api/explorer/user/'>User</a>
            </div>
            </div>
        </td>


        <td >
            <div class="post" style="width: 760px; min-height: 300px;">
                <div class="copy" >
                    {$msg}
                    {if $msg eq 'API_NO_METHOD'}
                        <table>

                            {foreach from=$METHODS item=CMETHOD}
                            <tr>
                                <td></td><td>{$CMETHOD}</td><td>()</td>
                            </tr>
                            {/foreach}
                        </table>
                    {/if}

                    {if $msg eq 'API_NO_CONTROLLER'}
                        here be the controllers
                    {/if}

                </div>
            </div>
        </td>
    </tr>
    </table>
</div>




{include file='footer.tpl'}


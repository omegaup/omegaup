{include file='head.tpl'}
{include file='mainmenu.tpl'}

<!--
<div class="post">
	<div class="copy " >
		concursos lista opciones,
		nuevo conruso
	</div>
</div>
-->
<div class="post">
	<div class="copy">
		<div class="POS Boton">Concursos activos</div>
		<div class="POS Boton">Todos los concursos</div>
		<div class="POS Boton">Crear un concurso</div>
	</div>
</div>

<div id="parent_contest_list">
	{include file='contest.list.tpl'}
</div>

{include file='footer.tpl'}

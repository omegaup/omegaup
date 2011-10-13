<?php 

class LoginComponent implements GuiComponent
{




	private $next_hop;




	function __construct()
	{
		$this->next_hop = ".";
	}




	public function submitTo( $submit_to )
	{
		$this->next_hop = $submit_to;
	}


	function renderCmp()
	{

		return  '<div align="center" >'
				. '<form action="' . $this->next_hop . '" method="POST">'
				. '<table border="0" cellspacing="5" cellpadding="5">'
				. '<tr><td rowspan="5"><img width="256" height="256" src="../../media/safe.png">'
				. '</td></tr><tr><td></td><td colspan=2><h2>Inicio de sesion</h2></td></tr><tr>'
				. '<td></td><td align="right">Usuario</td><td><input type="text" name="user" size="40"/>'
				. '</td></tr><tr><td></td><td align="right">Contrase&ntilde;a</td><td>'
				. '<input type="password" name="password" size="40" onkeypress=""/>'
				. '</td></tr><tr valign="top"><td></td><td></td><td align="right">'
				. '<input type="submit" value="Ingresar" onkeypress=""/>'
				. '</td></tr></table><input type="hidden" name="do_login" value=1></form></div>';

	}




}
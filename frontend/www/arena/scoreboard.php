<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena
 * 
 *
 * GET /arena/:contest_alias/scoreboard/
 * Regresa el HTML del scoreboard. Si el scoreboard no es público y
 * el usuario no esta loggeado, muestra el login. En cualquier otro
 * caso, muestra el scoreboard del concurso.
 *
 *
 * */


include('../ux/scoreboard.html');

<?php

/**
 * 
 * Please read full (and updated) documentation at: 
 * https://github.com/omegaup/omegaup/wiki/Arena
 * 
 *
 * GET /arena/:contest_alias/practice/
 * Regresa el HTML del concurso de práctica. Si el concurso no es público y
 * el usuario no esta loggeado, muestra el login. En cualquier otro
 * caso, muestra el concurso.
 *
 *
 * */


include('../ux/practice.html');

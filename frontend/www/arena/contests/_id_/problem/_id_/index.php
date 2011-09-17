<?php

/*
GET /contests/:id/problem/:id/
Si el usuario puede verlo, muestra el contenido del problema y referencias a las soluciones que ha enviado ese problema. Por el momento, propongo diferenciar los problemas que se esten usando en un concurso en vivo de los que son "estáticos" por la URI. Es decir, un problema en vivo siempre estará dentro de un concurso, es por eso que requiere su concurso/:id/... En cambio, un problema estático podrá ser accesado en un futuro solamente por /problems/:id,
*/


?>estas viendo el problema <?php echo $_GET["problem_id"]; ?> del contest <?php echo $_GET["contest_id"]; ?>

# Descripción

Karel es un ávido coleccionista de tarjetas de súper héroes.  Ayer decidió desempolvar su colección de los <b>Karel-Vengers</b>. Al abrir la caja dónde las tenía descubrió que las tarjetas están desordenadas e incompletas 
<b>:(</b>.

La colección de tarjetas completa tiene 100 tarjetas numeradas del 1 al 100. 
 
Resignado a que algunas tarjetas se le han perdido, Karel quiere saber 2 cosas:
<ul>
<li>Si tiene tarjetas repetidas.</li>
<li>Si las tarjetas que todavía le quedan son continuas en numeración.</li>
</ul>

En la primera fila del mundo de Karel, iniciando en la columna 1 habrá montones de zumbadores.  Cada montón representa el número de una tarjeta.  Los números pueden ir desde 1 hasta 100.  Las tarjetas están una junto de otra, es decir, no hay espacios con 0 zumbadores entre las tarjetas.

# Problema

Escribe un programa que, dada la lista de tarjetas verifique si Karel tiene tarjetas repetidas y si las tarjetas de Karel están continuas en numeración.

Si Karel tiene <b>REPETIDAS</b> o sus tarjetas <b>NO SON CONTINUAS</b> entonces deberá apagarse orientado al <b>SUR</b>.  

Si Karel <b>NO TIENE REPETIDAS</b> y sus tarjetas <b>SON CONTINUAS EN NUMERACIÓN</b> entonces deberá apagarse orientado al <b>NORTE</b>.


# Consideraciones
<ul>
<li>Karel empieza en la posición (1,1) orientado al norte.</li>

<li>Karel inicia con 0 zumbadores en su mochila.</li>

<li>La primera tarjeta siempre está en la posición (1,1).</li>

<li>El mundo mide 100 filas por 100 columnas y no tiene paredes internas.</li>

<li>Para obtener puntos <b>Karel debe apagarse viendo al SUR si hay REPETIDAS o NO SON CONTINUAS u orientado al NORTE si NO HAY REPETIDAS y todas SON CONTINUAS</b>.</li>

<li>No importan la posición final de Karel ni los zumbadores que dejes en el mundo.</li>

<li>En este problema los casos se agruparán de modo que cada grupo contenga al menos un caso cuya solución es terminar orientado al norte y un caso cuya solución sea terminar orientado al sur.</li>
</ul>


# Ejemplo1
![ejemplo 1](ejemplo1.png)

En este ejemplo Karel tiene las tarjetas <b>3, 4, 5, 6 y 7</b>.
<b>NO HAY REPETIDAS</b> y <b>TODAS SON CONTINUAS</b>.  Por lo tanto Karel debe apagarse orientado al <b>NORTE</b>.

# Ejemplo2
![ejemplo 2](ejemplo2.png)

En este ejemplo Karel tiene las tarjetas <b>3, 4, 5, 7 y 8</b>.
<b>NO HAY REPETIDAS</b> pero <b>FALTA LA TARJETA 6</b>.  Por lo tanto Karel debe apagarse orientado al <b>SUR</b>.
# Ejemplo3
![ejemplo 3](ejemplo3.png)

En este ejemplo Karel tiene las tarjetas <b>3, 4, 4, 5 y 6</b>.
La tarjeta <b>4</b> está <b>REPETIDA</b>.  Por lo tanto Karel debe apagarse orientado al <b>SUR</b>.

---

![illustration](illustration.png)

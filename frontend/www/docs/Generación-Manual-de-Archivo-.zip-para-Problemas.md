En este texto te mostramos cómo generar/editar manualmente un archivo `.zip` para un problema de omegaUp. Este texto está dirigido a aquellos usuarios más experimentados o que requieren de funcionalidades más específicas (por ejemplo, problemas de Karel). Si eres un usuario que va comenzando a escribir problemas o que tiene necesidades más básicas te recomendamos utilizar el [Creador de Problemas](https://mau-md.github.io/Omegaup-CDP/#/) y ver [este tutorial](https://www.youtube.com/watch?v=cUUP9DqQ1Vg&list=PL43fZBs80z1OdkZqSZte3vXA-8VKyh_ZZ&index=2&t=329s) de como usarlo. 

Si optaste por la opción manual que describiremos en este documento, también te recomendamos ver la [parte 1](https://www.youtube.com/watch?v=LfyRSsgrvNc) y [parte 2](https://www.youtube.com/watch?v=i2aqXXOW5ic) del tutorial de cómo crear problemas para omegaUp manualmente.

# Configuración

Los problemas de omegaUp tiene algunas variables que se pueden configurar:

* Validador:
    * **Token por token**: Lee todos los tokens (secuencias de hasta 4,194,304 caracteres imprimibles contiguos separados por espacios) del archivo de salida esperada y la salida del usuario y valida que ambas secuencias de tokens sean idénticas.
    * **Token por token, ignorando mayúsculas y minúsculas**: Lee todos los tokens (secuencias de hasta 4,194,304 caracteres imprimibles contiguos separados por espacios) del archivo de salida esperada y la salida del usuario, convierte todos los tokens a minúsculas, y valida que ambas secuencias de tokens sean idénticas.
    * **Tokens numéricos con tolerancia de 1e-9**: Lee todos los tokens numéricos (secuencias contiguas de números y separadores decimales) del archivo de salida esperada y la salida del usuario, interpreta todos los tokens como números, y valida que ambas secuencias de números tengan la misma longitud y que los números correspondientes en el archivo de salida esperada tengan un error absoluto O relativo de hasta 1e-9.
    * **Interpretar salida estándar como puntaje**: Lee la salida estándar, la convierte a un número flotante, la restringe al intervalo cerrado [0.0, 1.0] y usa eso como puntuación final. Normalmente sólo se utiliza con problemas interactivos, para evitar trampas del concursante.
    * **Validador personalizado (validator.$lang$)**: Permite proporcionar un programa que lee la salida estándar del concursante (y tanto la entrada estándar del caso como la salida esperada), e imprime un número flotante en el intervalo cerrado [0.0, 1.0]. Para ver cómo se debe escribir dicho programa, ver la sección de [validator.$lang$ (opcional)](#validatorlang-opcional).
* Lenguajes:
    * **C, C++, etc.**: Permite que el concursante proporcione el código fuente en uno de los lenguajes soportados para resolver el problema.
    * **Karel**: Permite que el concursante proporcione el código fuente en Karel para resolver el problema.
    * **Sólo salida**: Permite que el concursante proporcione un archivo .zip con las respuestas de todos los casos. Si se desea permitir que el concursante envíe un único caso de salida como texto en vez de un .zip, debe existir un sólo caso llamado `Main.in`/`Main.out`.
    * **Sin envíos**: No permite que el concursante haga envíos. Esto se utiliza únicamente para mostrar contenido en un curso.
* **Tiempo límite para el validador (ms)**: El número máximo de milisegundos (en tiempo real) que el evaluador esperará a que el validador emita un veredicto para cada caso antes de regresar `JE`.
* **Tiempo límite (ms)**: El número máximo de milisegundos (en tiempo de CPU) que el sistema operativo permitirá que el proceso del concursante se ejecute para cada caso antes de terminarlo con `TLE`.
* **Tiempo límite total (ms)**: El número máximo de milisegundos (en tiempo real) que el evaluador esperará a que el problema completo termine de ejecutar antes de terminarlo con `TLE`. Si algún caso no alcanzó a ejecutarse antes de este límite, no se evaluará. Para intentar tener cierta cantidad de consistencia, los casos se evaluarán en orden lexicográfico.
* **Tiempo extra para libinteractive (ms)**: El número máximo de milisegundos (en tiempo real) que el evaluador esperará a que el programa del evaluador termine para cada caso antes de terminarlo con `TLE`.
* **Límite de memoria (KiB)**: La cantidad máxima de memoria RAM (heap+stack) en [kibibytes](https://es.wikipedia.org/wiki/Kibibyte) que el sistema operativo permitirá que el programa del concursante utilice antes de terminarlo con `MLE`.
* **Límite de salida (bytes)**: El número máximo de bytes que el programa del concursante puede escribir a salida o error estándar antes de terminarlo con `OLE`. Normalmente este límite se autodetecta con los archivos `.out`, obteniendo el tamaño más grande de ellos y sumándole 10KiB. Para problemas que necesitan un validador personalizado, este valor se debe proporcionar explícitamente.
* **Límite de entrada (bytes)**: Longitud máxima (en bytes) del programa del concursante. Utilizado si se desea evitar que los concursantes utilicen una solución precalculada.
* **Fuente**: Atribución u origen de la redacción del problema.
* **Aparece en el listado público**: Si el problema se puede mostrar en el listado público y usar para concursos y cursos de terceros.
* **Enviar clarificaciones por correo**: Si omegaUp puede enviar clarificaciones que hagan los usuarios acerca de este problema por correo al autor del problema.
* **Tags**: Etiquetas de clasificación para este problema.

# Problemas de Lenguaje (C/C++/Java/Pascal)

Para subir un problema a omegaup hay que guardar los contenidos en un archivo **.ZIP** (no `.rar`, `.tar.bz2`, `.7z`, `.zx`). El nombre del zip no es importante.

El zip debe contener los siguientes elementos:

### cases/

* Esta carpeta debe contener todos los casos con extensiones .in y .out. El nombre de cada archivo no importa, pero los nombres deben estar correctamente pareados, por ejemplo: `1.in 1.out`, `hola.in hola.out`. 

* **El uso del `.` (punto) para un nombre de caso está prohibido, a menos que desees usar casos agrupados:** 

* omegaUp soporta casos agrupados. Es decir, para obtener puntos hay que resolver todos los casos de un sólo grupo. Este tipo de evaluación es útil cuando el conjunto de respuestas posibles para un problema es muy pequeño. Para agrupar, simplemente hay que separar el nombre del grupo con un `.` del nombre del caso. No hay límite en el número de grupos. 
Por ejemplo `grupo1.caso1.in grupo1.caso1.out grupo1.caso2.in grupo1.caso2.out` es un sólo grupo con 2 casos.
Los grupos pueden tener diferentes números de casos.

* No hay límite en el número de casos, sin embargo recomendamos mantener el tamaño total de los casos por debajo de los 100MB. 

Ten en cuenta que entre más casos, mayor tiempo se tomará en evaluar el problema para cada envío y puede causar una mala experiencia en el concurso debido al tiempo de espera en la cola para evaluar, en particular si una solución en la cola da `TLE`.

### statements/

* Debe contener la redacción del problema en formato markdown (el mismo formato que usa Wikipedia). El archivo se debe llamar `es.markdown`. Para previsualizar el formato puedes usar [https://omegaup.com/redaccion.php] (https://omegaup.com/redaccion.php) para ayudarse a previsualizar. 

* Soportamos LaTeX completamente. Puedes encontrar ejemplos de cómo usar LaTeX aquí [ http://www.thestudentroom.co.uk/wiki/LaTex] ( http://www.thestudentroom.co.uk/wiki/LaTex).

* Para dar una mejor experiencia a los concursantes, por favor asegúrate de que la previsualización se ve como deseas, incluyendo la tabla de casos de entrada y salida.

* Igualmente, encierra los nombres de variables en tu redacción así: `$n$`, `$x$`, etc... para que resalten de la redacción y sea fácil de localizar para los concursantes al momento del concurso, además de que evita confusiones. Para usar subíndices:  `$x_i$`

### solutions/
*  Es similar a **statements/**. Debe contener la redacción de la solución del problema en formato markdown. El archivo se debe llamar `es.markdown`, y en caso de tener traducciones `en.markdown` y `pt.markdown`.
* Tenemos ejemplos de archivos de problemas [aquí](https://github.com/omegaup/omegaup/tree/master/frontend/tests/resources) . En especial https://github.com/omegaup/omegaup/blob/master/frontend/tests/resources/testproblem.zip tiene un ejemplo con soluciones.

### interactive/ (opcional)

* Los problemas interactivos deben hacerse utilizando [libinteractive](https://omegaup.com/libinteractive/). Pueden encontrar más información en esa página.

* Para referencia de cómo debe estar estructurado un problema interactivo, pueden usar [Cave de la IOI 2013](https://omegaup.com/resources/cave.zip) como ejemplo.

### validator.$lang$ (opcional)

* Si tu problema necesita un validador personalizado, incluye un archivo llamado `validator.$lang$` en la raíz del zip, donde `$lang$` es uno de `c`, `cpp`, `java`, `p` (Pascal), `py`. Solo necesitas un validador, y es independiente del lenguaje del concursante.

* Dentro del validador, puedes abrir un archivo llamado `data.in`, que es el mismo archivo de entrada que se le dio al programa del concursante. En la entrada del validador (que puedes leer normal usando `scanf` o `cin`), se encuentra la salida del concursante. Es similar a como si se ejecutara `./concursante < data.in | ./validador nombredelcaso` en una consola, donde `nombredelcaso` es el nombre del `.in` del caso actual, pero sin la extensión.

* Adicionalmente puedes abrir un archivo llamado `data.out`, que contiene el `.out` asociado al `.in` actual.

* El código del validador **debe** escribir un número de punto flotante entre 0 y 1 a salida estándar, que indica el porcentaje del caso que el concursante tuvo bien. Si no escribes nada, resultará en un JE. Si escribes un valor menor que 0, el puntaje será 0, mientras que si escribes un valor mayor a 1, el puntaje será 1.

* Los validadores también corren en el sandbox, igual que los programas de los concursantes. Si llega a haber algún error con el código del validador (WA, RFE, RTE, etc.), el envío se juecea como JE.

* Aunque uses validador, debes proporcionar archivos .out (no se van a utilizar, puedes mandar archivos vacíos).

Para validar [sumas](https://omegaup.com/arena/problem/sumas), podrías usar el siguiente código en C++17:

```c++
#include <iostream>
#include <fstream>

int main() {
  // lee "data.in" para obtener la entrada original.
  int64_t a, b;
  {
    std::ifstream entrada_original("data.in", std::ifstream::in);
    entrada_original >> a >> b;
  }
  // puedes guardar cualquier cosa que te ayude a evaluar
  // en "data.out".
  int64_t suma;
  {
    std::ifstream salida_original("data.out", std::ifstream::in);
    salida_original >> suma;
  }

  // lee entrada estándar para obtener la salida del concursante.
  int64_t suma_concursante;
  if (!(std::cin >> suma_concursante)) {
    // cualquier cosa que imprimas a cerr se ignora, pero es útil
    // para depurar.
    std::cerr << "Error leyendo la salida del concursante\n";
    std::cout << 0.0 << '\n';
    return 0;
  }

  // determina si la respuesta es incorrecta.
  if (suma != suma_concursante && suma != a + b) {
    std::cerr << "Salida incorrecta\n";
    std::cout << 0.0 << '\n';
    return 0;
  }

  // Si la ejecución llega hasta aquí, la salida del concursante
  // es correcta.
  std::cout << 1.0 << '\n';
  return 0;
}
```

o Python 3:

```python
#!/usr/bin/python3
# -*- coding: utf-8 -*-

import logging
import sys

def _main():
  # lee "data.in" para obtener la entrada original.
  with open('data.in', 'r') as f:
    a, b = [int(x) for x in f.read().strip().split()]
  # puedes guardar cualquier cosa que te ayude a evaluar la
  # en "data.out".
  with open('data.out', 'r') as f:
    suma = int(f.read().strip())

  score = 0
  try:
    # Lee la salida del concursante
    suma_concursante = int(input().strip())

    # Determina si la salida es incorrecta
    if suma_concursante not in (suma, a + b):
      # Cualquier cosa que imprimas a sys.stderr se ignora, pero es útil
      # para depurar.
      print('Salida incorrecta', file=sys.stderr)
      return

    # Si la ejecución llega hasta aquí, la salida del concursante
    # es correcta.
    score = 1
  except:
    logging.exception('Error leyendo la salida del concursante')
  finally:
    print(score)

if __name__ == '__main__':
  _main()
```

### `testplan` (opcional)

* Por default, cada caso tiene un valor de 1/número-de-casos. Si deseas darle valores distintos a cada caso, crea un archivo llamado `testplan` (sin extensión) en la raíz del .zip. En este archivo, escribe una línea por caso. Cada línea debe tener el nombre del archivo que contiene el caso (sin la extensión) y los puntos para ese caso. Por ejemplo, para un problema con casos `cases/caso1.in`, `cases/grupo2.caso1.in`, `cases/grupo2.caso2.in`, el `testplan` sería:

    ```
    caso1 5
    grupo2.caso1 10
    grupo2.caso2 0
    ```

Asegúrate que ningún archivo tenga espacios en el nombre.

Si se desea asignar puntaje a un grupo (para no tener que dividirlo entre todos los casos del grupo), la convención es ponerle ese puntaje completo al primer caso del grupo y 0 a todos los demás casos de ese grupo.

## Imágenes

omegaUp ya tiene soporte nativo para imágenes :). Para insertar una imagen en tu redacción,  agrega el archivo de la imagen a tu zip dentro de la carpeta `statements/` y escribe en tu `es.markdown`:

`![Texto alternativo](imagen.jpg)`

Los formatos soportados son: jpg, gif, png. Cuida el tamaño de tu imagen, no se puede re-escalar en el markdown. Trata de que la imagen no pase de los 650 pixeles de ancho.

## Zips de ejemplo

Aquí hay algunos zips de ejemplo que usamos en los tests de omegaUp:

https://github.com/omegaup/omegaup/tree/master/frontend/tests/resources

## Errores y bugs conocidos de omegaUp

* Es de suprema importancia que las carpetas `/cases` y `/statements` estén directamente en la raíz del .zip, sin carpetas intermedias. [Bug link] (https://github.com/omegaup/omegaup/issues/310)  Una forma de hacerlo en la consola de Linux/Mac es con el comando `zip -r miproblema.zip *` desde el directorio del problema.
* omegaUp corre en Linux, así que sí hay diferencia entre mayúsculas y minúsculas. Si tu carpeta se llama `Cases`, no la va a encontrar, al igual que si tus archivos de entrada terminan en `.In`.

Cualquier duda, contacta a [joemmanuel](mailto:joemmanuel@gmail.com) y [lhchavez](mailto:lhchavez@lhchavez.com)

# Problemas de Karel
Primero intenta usar https://omegaup.com/karel.js/

Si ya tienes hechos los casos y te da flojera convertirlos con karel.js, estos puntos son para Windows. Antes que nada hay que tener instalado python 2.7 (http://www.python.org/download/releases/2.7.5/) y agregar al PATH (variable de entorno) la ruta de Python (que si le das next, next en la instalacion, la ruta por default es C:\Pyhton27) ...bueno teniendo python y que verifiques que desde la consola DOS, puedes hacer ejecutar pyhton con el comando "python" (sin comillas), pues ya puedes seguir con los puntos de abajo.

1. Tener estos archivos a la mano: https://docs.google.com/file/d/0B6Rb3__ksbxDRC1VSDV0amRYNmc/edit?usp=sharing . Son los exes de karel.exe (ejecutar una solucion con un mundo) y kcl.exe (compilador de soluciones), el script de python (karel_mdo_convert.py), y mi script (karel-to-omegaup.bat) que usa todo lo anterior.
2. Tener en una carpeta los casos MDO y los KEC; para generarlos puedes usar el karel que hace casos, no se si lo tengas, pero lo puedes bajar de aqui: http://www.cimat.mx/~amor/Omi/Utilerias/KarelOMI.zip
3. Teniendo eso, es necesario también tener la solución, yo programo en java, así que a mis soluciones les pongo extension .JS (esta extension es porque kcl.exe interpreta JS como un codigo hecho en karel-java), o si eres de pascal, agrega .PAS (para que kcl.exe interprete que es solucion en karel-pascal)
4. Ahora sí, hay que tener en la misma carpeta los exes, el script de python y mi script.
5. Mi script lo puedes ejecutar sin parametros, cuando entres te pedirá la ruta de la solucion (.JS o .PAS) y tambien te pedira la ruta de los casos (MDO y KEC) (esta esta ruta no es necesario agregar la ultima diagonal). También puedes correrlo de la consola con el comando: karel-to-omegaup.bat path-solucion path-casos . Si la ruta tiene espacios, utiliza comillas dobles para encerrar el path, por ejemplo:  

        karel-to-omegaup.bat "karel vs chuzpa\solucion.js" "karel vs chuzpa\casos"

6. Si todos los archivos están en su lugar, primero tratará de compilar la solucion.js (usando kcl.exe que genera un archivo .KX con el mismo nombre y en la misma ruta de la solucion), luego creará los mundos .IN usando los MDO (busca todos los archivos con extension MDO que se encuentren en la carpeta "path-casos" ). Un punto importante es que el script de python (karel_mdo_convert.py) necesita que el KEC exista, es decir si el MDO se llama caso1.MDO es necesario que exista caso1.KEC. Si eso esta bien, el script de python extrae la información de beepers, orientacion, y posicion, si esta informacion existe, se la agrega al archivo IN que genera.
8. Una vez que se genera el archivo IN, mi script ejecuta karel.exe usando el archivo IN que acaba de generar y la solución compilada (con extension KX) como parametro y con ello te genera el archivo OUT para ese IN. Es necesario que la solucion este bien, ya que de esta depende que se genere bien el OUT.
9. Mi script BAT, crea una carpeta "cases" dentro de la carpeta de que contenia los casos, y ahi se van guardando los IN y OUT para Karel.
10. Listo ya tienes la carpeta cases con los IN y OUT, ya solo crea la carpeta statements con el es.markdown y que comprimas como cuando haces problemas de Lenguaje.
Arena es un servicio web al que Frontend tiene que llamar para todo lo relacionado con concursos (y un poco de administración de ellos, como modificación de problemas, etc.). Arena va a ser parte de Frontend durante la v1, y será un componente aparte a partir de la v2.

# Generalidades

* Dado que sólo tenemos SSL en omegaup.com, la API estará bajo el path *https://omegaup.com/api/*
* La mayoría de las llamadas al API necesitan un parámetro `auth_token`, que se puede obtener llamando a `/api/user/login/` o haciendo login en la página normal.
* El manejo de sesiones será a través de `POST`, pero también se pueden usar cookies para que las llamadas que son `GET` puedan usar autenticación.
* El envío de parámetros será a través de *JSON*.
* Se necesita enviar el parámetro `auth_token` con un token válido para todas las llamadas que requieran autenticación.

# Errores

* Todas las respuestas tienen un campo llamado `status`.
Cuando las llamadas sean exitosas tendrán el valor literal `ok`.
Cuando exista algún error el valor de `status` será `error` y habrá un campo llamado `error` que contendrá una descripción legible del error (en el idioma que se tenga configurada la cuenta), un campo `errorcode` y `errorname` que tendrán tanto  un identificador numérico, como uno de texto del error.

* Además,  el servidor establecerá el status HTTP al valor apropiado en los siguientes casos:


| Código | Respuesta               | Descripción                                                                      |
| ------ | ----------------------- | -------------------------------------------------------------------------------- |
| 200    | OK                      | No hubo ningún problema y la petición es exitosa.                                |
| 400    | BAD REQUEST             | La petición (incluyendo el mensaje JSON) está malformada.                        |
| 401    | AUTHENTICATION REQUIRED | La petición carece del campo `auth_token`, ya sea mediante un Cookie o mediante la petición JSON. |
| 403    | FORBIDDEN               | Se encontró el recurso buscado, pero no se tienen los privilegios necesarios para accederlos, o modificarlos (como un usuario queriendo leer los runs ajenos, o entrar al panel de administración del concurso). |
| 404    | NOT FOUND               | No se encontró el recurso buscado (un usuario, un problema, un concurso, un run, etc.), o se encontró pero está deliberadamente escondido (como el caso de los concursos privados). |
| 505    | INTERNAL SERVER ERROR   | La petición terminó de manera inesperada. Puede que la respuesta incluso esté vacía, o la descripción sea ambigua. Esperemos que en los logs haya más información al respecto de este error. |

# Authentication

### POST `/api/user/login`
Se envía el nombre de usuario (o correo electrónico) y su contraseña, se recibe auth token. Los auth tokens serán validos por 24 horas y será una cadena de no mas de 128 caracteres.

#### Parámetros
  * `usernameOrEmail` El nombre de usuario o su correo electrónico en texto plano.  
  * `password` La contraseña del usuario en texto plano. 

#### Regresa
  * `auth_token` Token para esta sesión.

# Contest
### GET `/api/contest/list`

Lista (por default de los últimos 20 concursos) que el usuario "puede ver".

#### Parámetros
Puede recibir distintos parámetros, dependiendo de la lista de concursos que se desean visualizar.
  * `active`: [`ACTIVE`, `FUTURE`, `PAST`] Indica que que concursos se desean mostrar. 
  * `recommended`: [`RECOMMENDED`, `NOT_RECOMMENDED`] Indica si se desean mostrar los concursos recomendados. 
  * `participating`: [`YES`, `NO`] Indica si se desean mostrar los concursos en los cuales el usuario está participando.
  * `public`: [`YES`, `NO`] Indica si se desean mostrar los cursos públicos o en donde el usuario ha sido registrado.

#### Salida
    {
        'number_of_results': int // Número de resultados que se muestran
        'results': [
            {
                'contest_id' : int // Id del concurso
                'problemset_id' : int // Id del conjunto de problemas
                'alias': string // Alias del concurso, necesario para acceder
                'title': string // Título de cada concurso
                'description': string // Descripción de cada concurso
                'start_time': int // Hora de inicio en (timestamp) 
                'finish_time': int // Hora de terminación del concurso en (timestamp)
                'last_updated': int // Hora en que se modificó por última vez el concurso (timestamp)
                'original_finish_time': datetime // Hora de terminación del concurso
                'admission_mode': enum['public', 'private', 'registration'] // Indica si el concurso es público, privado o
                                                                            // requiere de registro por parte del usuario.
                'recommended': bool // Indica si el concurso es recomendado
                'duration': int // Indica el tiempo que estará disponible el concurso (muestra la diferencia entre la hora
                                // de inicio y la hora de terminación)
                'window_length': int // Indica el tiempo que estará disponible el concurso una vez que este sea abierto por 
                                     // el usuario (regresará `null` si el concurso no fue configurado con la característica)
            },
            ...
        ]
    }

### POST `/api/contest/create`
Si el usuario cuenta con un `auth_token` crea un nuevo concurso, sin problemas asociados.

#### Parámetros
    {
         'auth_token' : string // Se requiere que el usuario esté logueado
         'title' : string // Título del concurso
         'description' : string // Una breve descripción de la finalidad del concurso
         'start_time' : datetime // Hora de inicio del concurso
         'finish_time' : datetime // Hora de final del concurso
         'window_length' : int // Opcional si cada usuario tendrá el mismo tiempo para realizar el concurso sin importar en 
                               // que momento ingresa
         'alias' : string(32) // Almacenará el alias necesario para acceder al concurso
         'points_decay_factor : double (0,1)
         'submissions_gap' : int (0, finish_time - start_time) // Tiempo mínimo en segundos que debe de esperar un usuario 
                                                               // después de realizar un enví­o para hacer otro
         'feedback' : enum (no, yes, partial) 
         'penalty' : int (0, INF) // Entero indicando el número de minutos con que se penaliza por recibir un no-accepted
         'public' : bool // Concurso público o no. Por defecto el concurso será privado y no podrá ser público hasta que 
                         // se hayan agregado problemas
         'scoreboard' : int (0,100) // Porcentaje de tiempo durante el cual el scoreboard es visible
         'penalty_type' : enum (none, problem_open, contest_start, runtime) // Indica cómo se calcula la penalización por 
                                                                            // envío 
         'show_scoreboard_after' : bool // Indica si se va a mostrar el scoreboard completo al finalizar el concurso
         'languages' : set (kp, kj, c11-gcc, c11-clang, ...) // Establece los lenguajes para el concurso, se pueden 
                                                             // establecer más de uno, separando con comas cada uno
         'basic_information' : bool // Indica si los usuarios deben haber registrado su información básica para poder 
                                    // unirse al oncurso (la información básica consiste en País, Estado, Escuela)
         'requests_user_information' : enum (no, optional, required) // Indica si el organizador solicitará permiso para 
                                                                     // visualizar la información de los concursantes
    }

### GET `/api/contest/publicdetails/`
Si el usuario puede verlos, muestra los detalles del concurso :contest_alias (info mínima de los problemas, tiempo restante, mini-ranking… un query sencillito, carismático y cacheable).

#### Parámetros
  * `contest_alias` : string // El alias del concurso del cual se desean obtener los detalles públicos

#### Salida
    {       
        'alias': string // Almacenará el alias necesario para acceder al concurso
        'title': string // Título de cada concurso
        'description': string // Descripción de cada concurso
        'start_time': datetime // Hora de inicio
        'finish_time': datetime // Hora de terminación del concurso
        'window_length' : int // Indica el tiempo que tiene el usuario para enviar solución, si es NULL entonces será 
                              // durante todo el tiempo del concurso
        'scoreboard': int // Entero del 0 al 100, indicando el porcentaje de tiempo que el scoreboard será visible
        'points_decay_factor': int // El factor de decaimiento de los puntos de este concurso. El default es 0 (no decae). 
                                   // TopCoder es 0.7
        'partial_score' : bool // Verdadero si el usuario recibirá puntaje parcial para problemas no resueltos en todos 
                               // los casos
        'sumbissions_gap : int // Tiempo mínimo en segundos que debe de esperar un usuario después de realizar un envío
                               // para hacer otro
        'feedback' : enum (yes, no)
        'penalty' : int // Entero indicando el número de minutos con que se penaliza por recibir un no-accepted
        'penalty_time_start' : int // Indica el momento cuando se inicia a contar el timpo: cuando inicia el concurso o 
                                   // cuando se abre el problema
        'penalty_calc_policy' : enum ('sum', 'max')
        'admission_mode' : enum (public, private, registration)
    }

### GET `/api/problemset/scoreboard/`
Si el usuario tiene los permisos adecuados, se muestra el ranking completo del concurso con ese id de conjunto de problemas.

#### Parámetros
  * `problemset_id`  : int // Id del conjunto de problemas 
  * `auth_token` : string (opcional) // Token para esta sesión.

#### Salida
    {
         'problems' : [
             {
                 order : int // Util para ordenar los problemas
                 alias : string // Util para ordenar problemas alfabéticamente
             }
             ...
         ],
         'ranking' : [
             {
                 'username': string, // El nombre de usuario
                 'name': string, // El nombre a desplegar en el ranking
                 'country' : string // País del usuario
                 'classname' : string // Rango que se ha ganado el usuario de acuerdo a su trayectoria en la plataforma
                 'is_invited' : bool // Indica si el usuario fue invitado explícitamente o ingresó a concurso público
                 'total': {
                     'points': double, // La cantidad total de puntos del usuario
                     'penalty': double // El penalty total acumulado
                 }
                 'problems': [
                     {
                         'alias' : string
                         'points' : double, // La cantidad total de puntos del usuario para ese problema
                         'penalty' : double // El penalty que ese usuario acumuló para ese problema
                         'percent' : int
                         'runs' : int // Número de envíos realizados de este problema en este concurso por este usuario
                     },
                     ...
                 ]
             },
             ...
         ]
    }

### GET `/api/problemset/scoreboardevents/`
Si el usuario tiene los permisos adecuados, regresa una lista de todos los eventos que causaron el puntaje de alguien cambiara.

#### Parámetros
  * `problemset_id` : int // Id del conjunto de problemas 
  * `auth_token` : string (opcional) // Token para esta sesión.

#### Salida

    {
         'events' : [
             {
                 'username': string, // El nombre de usuario
                 'name': string, // El nombre a desplegar en el ranking
                 'delta': int, // El número de segundos a partir del inicio del concurso en el cual ocurrió este evento
                 'total': {
                     'points': double, // La cantidad total de puntos del usuario
                     'penalty': double // El penalty total acumulado
                 }
                 'problem': {
                     'alias': string, // El nombre del problema con el que ocurrió 
                     'points': double, // La cantidad total de puntos del usuario para ese problema
                     'penalty': double // El penalty que ese usuario acumuló para ese problema
                  }
                 'country' : string // País del usuario
                 'classname' : string // Rango que se ha ganado el usuario de acuerdo a su trayectoria en la plataforma
                 'is_invited' : bool // Indica si el usuario fue invitado explícitamente o ingresó a concurso público
             },
             ...
         ]
    }   

# Problems
### GET `/api/problem/details/`
Si el usuario tiene los permisos adecuados, muestra el contenido del problema y referencias a las soluciones que ha enviado ese problema.

#### Parámetros
  * `problem_alias` : string // El alias del problema que se desean mostrar los detalles.

#### Salida
    {
        'title' : string 
        'alias' : string 
        'input_limit' : int
        'validator' : enum('remote','literal','token','token-caseless','token-numeric')
        'time_limit' : int
        'memory_limit' : int
        'visits' : int
        'submissions' : int 
        'accepted' : int
        'difficulty' : double
        'creation_date' : datetime
        'source' : string //El autor o concurso original del problema
        'order' : enum('normal','inverse')
        'visibility' : int
        'email_clarifications' : bool
        'quality_seal' : bool
        'version' : string
        'commit' : string
        'problemsetter' : {
            'username' : string
            'name' : string
            'creation_date' : int // Timestamp 
        }
        'statement' : {
            'language' : string
            'images' : []
            'markdown' : string
        }
        'runs' : [
             {
                  'guid' : string // Identificador del run
                  'language' : enum('c','cpp','java','py','rb','pl','cs','p')
                  'status' : enum('new','waiting','compiling','running','ready')
                  'veredict' : enum('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
                  'runtime' : int
                  'memory' : int
                  'score' : double
                  'contest_score' : double
                  'ip': string
                  'time' : datetime
                  'submit_delay' : int // es el numero de minutos desde que se abrio el probblema hasta que lo mando.
             }
             ...
         ]
         'languages' : [
              java, py2, py3, rb, ...
         ],
         'points' : double
         'score' : int
         'exists' : bool
         'settings' : {
              'cases' : {
                  'sample' : {
                      'in' : string
                      'out' : string
                      'weight' : int
                  }
                  ...
              }
              'limits' : {
                  'ExtraWallTime' : string
                  'MemoryLimit' : string
                  'OutputLimit' : string
                  'OverallWallTimeLimit' : string
                  'TimeLimit' : string
              }
              'validator' : {
                  'name' : string
                  'tolerance' : string
              }
         }
    }

### POST `/api/problem/create/`
Si el usuario cuenta con un `auth_token` válido, crea un nuevo problema que después podrá ser asociado a un concurso o curso

#### Parámetros
    {
        'title' : string
        'alias' : string
        'source' : el autor o concurso original del problema
        $_FILES['problem_contents']      
        'validator' : enum ('remote','literal','token','token-caseless','token-numeric' ) - opcional (default 'token')
        'languages' : enum ('c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb', 'kj, kp', 'cat', '') 
                      - opcional (default 'c11-clang,c11-gcc,cpp11-clang,cpp11-gcc,cpp17-clang,cpp17-gcc,cs,hs,java,lua,pas,py2,py3,rb')
        'validator_time_limit' : int - opcional (default 1000)
        'time_limit' : int (ms) - opcional (default 1000)
        'overall_wall_time_limit' : int (ms) - opcional (default 60000)
        'extra_wall_time' : int (ms) - opcional (default 0)
        'memory_limit' : int (KiB) - opcional (default 32768)
        'output_limit' : int (bytes) - opcional (default 10240)
        'input_limit' : int (bytes) - opcional (default 10240)
        'order' : string enum('normal','inverse') - opcional (default normal)
        'visibility' : int - opcional (default 0 - privado)
        'tags' : []
    }

# Runs
### POST `/api/run/create/`
En caso de estar logueado, El usuario envía una solución.

#### Parámetros
  * `auth_token` : string // Token para esta sesión.
  * `problem_alias` : string // El alias del problema al cual se pretende enviar una solución.
  * `language` : string // El lenguaje [*] en el que se envía la solución.
  * `source` : string // El código que da solución al problema seleccionado.
  * `contest_alias` : string - (opcional) // El alias del concurso, en caso de que el problema pertenezca al conjunto de problemas agregados a este concurso.  

#### Salida
    {
        'submission_deadline' : int // Tiempo límite para realizar envíos (Timestamp). Este será 0 en caso de no estar dentro de un concurso
        'nextSubmissionTimestamp' : int // Tiempo en lo que el usuario puede realizar un nuevo envío de este problema (Timestamp)
        'guid' : string
    }

### GET `/api/problem/runs/`
Si el usuario tiene permiso, regresa las referencias a las últimas soluciones a un problema en particular que el mismo usuario ha enviado, y su estado y calificación.

#### Parámetros
  * `auth_token` : string // Token para esta sesión.
  * `problem_alias` : string // El alias del problema al cual se desean mostrar los envíos realizados.

#### Salida
    {
        'runs' : [
            {
                'guid' : string
                'language' : string
                'status' : string
                'verdict' : enum ('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
                'runtime' : int
                'penalty' : int
                'memory' : int
                'score' : double
                'contest_score' : double
                'time' : int
                'submit_delay' : int
                'alias' : string
                'username' : string
            }
            ...
        ]
    }

### GET `/api/run/details/`
Si el usuario tiene permiso, puede ver su solución y el estado de la misma. 

#### Parámetros
  * `auth_token` : string // Token para esta sesión.
  * `run_alias` : string // El alias del envío del cuál se desean mostrar los detalles.

#### Salida
    {
        'admin' : bool
        'guid' : string
        'language' : string
        'source' : string
        'details' : {
            'verdict' : enum ('AC','PA','PE','WA','TLE','OLE','MLE','RTE','RFE','CE','JE')
            'compile_meta' : {
                'verdict' : string
                'time' : double
                'sys_time' : double
                'wall_time' : double
                'memory' : int
            }
            'score' : double
            'contest_score' : double
            'max_score' : double
            'time' : double
            'wall_time' : double
            'memory' : int
            'judged_by' : string
        }
    }

# Clarifications
### POST `/api/clarification/create/`
Si el usuario tiene permiso, envía una clarificación sobre un problema en particular.  En los parámetros se envía el ID del problema. La API regresa un ID para trackearla de alguna forma.

#### Parámetros
    {
        'auth_token' : string
        'problem_alias' : string
        'contest_alias' : string (opcional si el problema no está dentro de un concurso)
        'message' : string
    }

#### Salida
    {
        'clarification_id' : int // id de la clarificación recién enviada
    }

### GET `/api/problem/clarifications/`
Regresa TODAS las clarificaciones de un problema en particular, a las cuales el usuario puede ver (equivale a las que el personalmente mandó más todas las clarificaciones del problema marcadas como públicas)


#### Parámetros
    {
        'auth_token' : string
        'problem_alias' : string
        'offset' : int - Opcional (default 0)
        'rowcount' : int - Opcional (default 20)
    }

#### Salida
    {
        'clarifications' : [
             {
                 'clarification_id' : int // id de la clarificación recién enviada
                 'contest_alias' : string // En caso de que la clarificación se haya realizado desde un concurso
                 'author' : string
                 'message' : string
                 'answer' : null|string
                 'time' : int
                 'public' : bool
             }
             ...
        ]
    }

### POST `/api/clarification/update/`
Si el usuario es el creador del problema o del concurso puede responder las clarificaciones.

#### Parámetros
    {
        'auth_token' : string
        'clarification_id' : int
        'answer' : string
        'public' : bool
    }

#### Salida
    {
        'status' : string
    }

### GET `/api/contest/clarifications/`
Regresa TODAS las clarificaciones de un concurso en particular, a las cuales el usuario puede ver (equivale a las que el personalmente mandó más todas las clarificaciones del concurso marcadas como públicas)

#### Parámetros
    {
        'auth_token' : string
        'contest_alias' : string
        'offset' : int - Opcional (default 0)
        'rowcount' : int - Opcional (default 20)
    }

#### Salida
    {
        'clarifications' : [
             {
                 'clarification_id' : int // id de la clarificación recién enviada
                 'contest_alias' : string // En caso de que la clarificación se haya realizado desde un concurso
                 'author' : string
                 'receiver' : null|string
                 'message' : string
                 'answer' : null|string
                 'time' : int
                 'public' : bool
             }
             ...
        ]
    }


# Regresan HTML:
### GET `/arena/`
Regresa el HTML de la arena. Si el usuario no esta loggeado, muestra el listado de concursos públicos actuales. En caso de estar logueado el usuario, se muestra el listado de los concursos a los cuales pertenece.
### GET `/arena/:contest_alias`
Si el usuario no está logueado, se muestra el intro con los detalles del concurso y un botón de Iniciar sesión. Si el usuario ya está logueado, se mostrará el mismo intro con los detalles, pero el botón será para Iniciar el concurso
### GET `/arena/:contest_alias/scoreboard`
Si el usuario puede verlo, regresa el HTML asociado a un concurso, arreglando de forma gráfica los contenidos de `/api/problemset/scoreboard/`.
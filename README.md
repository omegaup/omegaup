[![omegaUp](frontend/www/media/omegaup.png)](https://omegaup.com)
[![Contributors](https://img.shields.io/github/contributors/omegaup/omegaup)](https://github.com/omegaup/omegaup/graphs/contributors) [![Issues open](https://img.shields.io/github/issues/omegaup/omegaup)](https://github.com/omegaup/omegaup/issues?q=is%3Aissue+is%3Aopen) [![Issues closed](https://img.shields.io/github/issues-closed/omegaup/omegaup)](https://github.com/omegaup/omegaup/issues?q=is%3Aissue+is%3Aclosed)

[![Forks](https://img.shields.io/github/forks/omegaup/omegaup?style=social)](https://github.com/omegaup/omegaup/network/members) [![Stars](https://img.shields.io/github/stars/omegaup/omegaup?style=social)](https://github.com/omegaup/omegaup/stargazers) [![Twitter](https://img.shields.io/twitter/follow/omegaup.svg?style=social&label=Follow)](https://twitter.com/omegaup)

Ver el contenido en Español: [Español](#Español)  
View the content in English: [English](#English)  
Veja o conteúdo em Português: [Português](#Português)  
Vedi il contenuto in Italiano: [Italiano](#Italiano)

# Español
[omegaUp](https://omegaup.com) es una plataforma educativa gratuita que ayuda a mejorar las habilidades en programación, usada por decenas de miles de estudiantes y docentes en Latinoamérica.

## Directorios

Directorios que se utilizan activamente en el desarrollo.

| Directorio | Descripción |
|------------|-------------|
| [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers) | Lógica de negocio que implementa la API de omegaUp. |
| [frontend/server/libs](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs) | Bibliotecas y utilerías. |
| [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO) | Los Data Access Objects [DAO] y Value Objects [VO]. Clases utilizadas para representar los esquemas de la base de datos y facilitar su consumo por los controladores. |
| [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates) | Plantillas utilizadas para generar el HTML que se despliega a los usuarios. También aquí están los archivos de internacionalización para inglés, español y portugués. |
| [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www) |  Los contenidos completos de la página de internet. |

El resto del código está en otros repositorios

| Repositorio| Descripción |
|------------|-------------|
| [quark](https://github.com/omegaup/quark) | Incluye el código del grader para la calificación de problemas y ejecutar los códigos bajo minijail, así como el servicio utilizado en los servidores de la nube para servir la cola de envíos. |
| [karel.js](https://github.com/omegaup/karel.js) | La versión oficial de Karel utilizada por la Olimpiada Mexicana de Informática. |
| [omegajail](https://github.com/omegaup/omegajail) | Un mecanismo de ejecución segura que basado en contenedores de Linux y seccomp-bpf. Utiliza [minijail](https://android.googlesource.com/platform/external/minijail/+/master), escrito por el proyecto [Chromium](https://www.chromium.org). |
| [libinteractive](https://github.com/omegaup/libinteractive) | Una librería para hacer problemas interactivos fácilmente.

## Navegadores Soportados

Los navegadores oficialmente soportados son aquellos que soportan [ECMAScript 2015 (ES6)](https://caniuse.com/#feat=es6), e incluyen los siguientes:

| Navegador | Versión |
|-----------|---------|
| [Chrome](https://www.google.com/chrome/) | 51 |
|[Firefox](http://mozilla.org/firefox/releases/) | 68 |
| [Edge](https://www.microsoft.com/edge) | 12 |
| [Safari](https://www.apple.com/safari/) | 12 |

Esto también incluye todos los navegadores basados en Blink / WebKit cuyas versiones sean compatibles con las de Chrome / Safari.

## Licencia

BSD

# English
[omegaUp](https://omegaup.com) is a free educational platform that helps improve programming skills, used by tens of thousands of students and teachers in Latin America.

## Directories

Directories that are actively used in development.

| Directory | Description |
|------------|-------------|
| [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers) | Business logic that implements the omegaUp API. |
| [frontend/server/libs](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs) | Libraries and props. |
| [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO) | Data Access Objects [DAO] and Value Objects [VO]. Classes used to represent database schemas and facilitate their consumption by controllers. |
| [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates) | Templates used to generate the HTML that is displayed to users. Also here are the internationalization files for English, Spanish and Portuguese. |
| [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www) | The complete contents of the website. |

The rest of the code is in other repositories

| Repository| Description |
|------------|-------------|
| [quark](https://github.com/omegaup/quark) | It includes the grader code for rating issues and running the codes under minijail, as well as the service used on the cloud servers to serve the submission queue. |
| [karel.js](https://github.com/omegaup/karel.js) | The official version of Karel used by the Mexican Informatics Olympiad. |
| [omegajail](https://github.com/omegaup/omegajail) | A secure execution mechanism based on Linux containers and seccomp-bpf. It uses [minijail](https://android.googlesource.com/platform/external/minijail/+/master), written by the [Chromium](https://www.chromium.org) project. |
| [libinteractive](https://github.com/omegaup/libinteractive) | A library to easily do interactive problems.

## Supported Browsers

Officially supported browsers are those that support [ECMAScript 2015 (ES6)](https://caniuse.com/#feat=es6), and include the following:

| Browser | Version |
|-----------|---------|
| [Chrome](https://www.google.com/chrome/) | 51 |
|[Firefox](http://mozilla.org/firefox/releases/) | 68 |
| [Edge](https://www.microsoft.com/edge) | 12 |
| [Safari](https://www.apple.com/safari/) | 12 |

This also includes all Blink/WebKit-based browsers whose versions are compatible with Chrome/Safari.

## License

BSD

# Português
[omegaUp](https://omegaup.com) é uma plataforma educacional gratuita que ajuda a melhorar as habilidades de programação, usada por dezenas de milhares de estudantes e professores na América Latina.

## Diretórios

Diretórios que são usados ​​ativamente no desenvolvimento.

| Diretório | Descrição |
|------------|-------------|
| [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers) | Lógica de negócios que implementa a API omegaUp. |
| [frontend/server/libs](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs) | Bibliotecas e adereços. |
| [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO) | Objetos de acesso a dados [DAO] e objetos de valor [VO]. Classes utilizadas para representar esquemas de banco de dados e facilitar seu consumo pelos controladores. |
| [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates) | Modelos usados ​​para gerar o HTML que é exibido aos usuários. Aqui também estão os arquivos de internacionalização para inglês, espanhol e português. |
| [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www) | O conteúdo completo do site. |

O resto do código está em outros repositórios

| Repositório| Descrição |
|------------|-------------|
| [quark](https://github.com/omegaup/quark) | Inclui o código do avaliador para avaliar problemas e executar os códigos no minijail, bem como o serviço usado nos servidores em nuvem para atender a fila de envio. |
| [karel.js](https://github.com/omegaup/karel.js) | A versão oficial do Karel usada pela Olimpíada Mexicana de Informática. |
| [omegajail](https://github.com/omegaup/omegajail) | Um mecanismo de execução seguro baseado em contêineres Linux e seccomp-bpf. Ele usa [minijail](https://android.googlesource.com/platform/external/minijail/+/master), escrito pelo projeto [Chromium](https://www.chromium.org). |
| [libinterativo](https://github.com/omegaup/libinterativo) | Uma biblioteca para resolver facilmente problemas interativos.

## Navegadores Suportados

Os navegadores oficialmente suportados são aqueles que suportam [ECMAScript 2015 (ES6)](https://caniuse.com/#feat=es6) e incluem o seguinte:

| Navegador | Versão |
|-----------|---------|
| [Chrome](https://www.google.com/chrome/) | 51 |
|[Firefox](http://mozilla.org/firefox/releases/) | 68 |
| [Edge](https://www.microsoft.com/edge) | 12 |
| [Safari](https://www.apple.com/safari/) | 12 |

Isso também inclui todos os navegadores baseados em Blink/WebKit cujas versões são compatíveis com Chrome/Safari.

## Licença

BSD

# Italiano
[omegaUp](https://omegaup.com) è una piattaforma educativa gratuita che aiuta a migliorare le abilità nella programmazione, usata da decine di migliaia di studenti ed insegnanti in America Latina.

## Cartelle

Le cartelle che sono attivamente utilizzate nello sviluppo.

| Cartella | Descrizione |
|------------|-------------|
| [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers) | Logica di business che implementa le API di omegaUp. |
| [frontend/server/libs](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs) | Librerie e utility. |
| [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO) | I Data Access Objects [DAO] e Value Objects [VO]. Classi utilizzate per rappresentare gli schemi del database e facilitare il loro utilizzo da parte dei controller. |
| [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates) | Modelli utilizzati per generare l'HTML visualizzato agli utenti. Sono anche presenti i file di internazionalizzazione per inglese, spagnolo e portoghese. |
| [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www) |  L'intero contenuto del sito web. |

Il resto del codice si trova in altre repository.

| Repository| Descrizione |
|------------|-------------|
| [quark](https://github.com/omegaup/quark) | Include il codice del grader per la valutazione dei problemi e l'esecuzione dei codici sotto minijail, così come il servizio utilizzato nei server cloud per gestire la coda delle sottomissioni. |
| [karel.js](https://github.com/omegaup/karel.js) | La versione ufficiale di Karel utilizzata dall'Olimpiade Messicana di Informatica. |
| [omegajail](https://github.com/omegaup/omegajail) | Un meccanismo di esecuzione sicura basato su container Linux e seccomp-bpf. Utilizza [minijail](https://android.googlesource.com/platform/external/minijail/+/master), sviluppato dal progetto [Chromium](https://www.chromium.org). |
| [libinteractive](https://github.com/omegaup/libinteractive) | Una libreria per creare facilmente problemi interattivi.

## Browser supportati

I browser ufficialmente supportati sono quelli che supportano [ECMAScript 2015 (ES6)](https://caniuse.com/#feat=es6), e comprendono i seguenti:

| Browser | Versione |
|-----------|---------|
| [Chrome](https://www.google.com/chrome/) | 51 |
|[Firefox](http://mozilla.org/firefox/releases/) | 68 |
| [Edge](https://www.microsoft.com/edge) | 12 |
| [Safari](https://www.apple.com/safari/) | 12 |

Questo include anche tutti i browser basati su Blink/WebKit le cui versioni sono compatibili con quelle di Chrome/Safari.

## Licenza

BSD
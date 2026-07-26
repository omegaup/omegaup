# [![omegaUp](frontend/www/media/omegaup.png)](https://omegaup.com)

[![Contributors](https://img.shields.io/github/contributors/omegaup/omegaup?style=for-the-badge&logo=github&color=blue)](https://github.com/omegaup/omegaup/graphs/contributors)
[![Issues Open](https://img.shields.io/github/issues/omegaup/omegaup?style=for-the-badge&logo=github&color=orange)](https://github.com/omegaup/omegaup/issues?q=is%3Aissue+is%3Aopen)
[![Issues Closed](https://img.shields.io/github/issues-closed/omegaup/omegaup?style=for-the-badge&logo=github&color=green)](https://github.com/omegaup/omegaup/issues?q=is%3Aissue+is%3Aclosed)
[![Forks](https://img.shields.io/github/forks/omegaup/omegaup?style=for-the-badge&logo=github&color=purple)](https://github.com/omegaup/omegaup/network/members)
[![Stars](https://img.shields.io/github/stars/omegaup/omegaup?style=for-the-badge&logo=github&color=gold)](https://github.com/omegaup/omegaup/stargazers)
[![Twitter](https://img.shields.io/twitter/follow/omegaup.svg?style=for-the-badge&logo=x&color=1DA1F2)](https://twitter.com/omegaup)

---

🌐 **Language Navigation / Navegación por idioma:**

[Español](#Español) • [English](#English) • [Português](#Português) • [Italiano](#Italiano)

---

# 🇪🇸 Español

[omegaUp](https://omegaup.com) es una plataforma educativa gratuita que ayuda a mejorar las habilidades en programación, usada por decenas de miles de estudiantes y docentes en Latinoamérica.

## 📁 Directorios

Directorios que se utilizan activamente en el desarrollo.

| Directorio | Descripción |
| :--- | :--- |
| [frontend/server/src/Controllers](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/Controllers) | Lógica de negocio que implementa la API de omegaUp. |
| [frontend/server/libs](https://github.com/omegaup/omegaup/tree/main/frontend/server/libs) | Bibliotecas y utilerías. |
| [frontend/server/src/DAO](https://github.com/omegaup/omegaup/tree/main/frontend/server/src/DAO) | Los Data Access Objects [DAO] y Value Objects [VO]. Clases utilizadas para representar los esquemas de la base de datos y facilitar su consumo por los controladores. |
| [frontend/templates](https://github.com/omegaup/omegaup/tree/main/frontend/templates) | Plantillas utilizadas para generar el HTML que se despliega a los usuarios. También aquí están los archivos de internacionalización para inglés, español y portugués. |
| [frontend/www](https://github.com/omegaup/omegaup/tree/main/frontend/www) | Los contenidos completos de la página de internet. |

### 🔗 El resto del código está en otros repositorios

| Repositorio | Descripción |
| :--- | :--- |
| [quark](https://github.com/omegaup/quark) | Incluye el código del grader para la calificación de problemas y ejecutar los códigos bajo minijail, así como el servicio utilizado en los servidores de la nube para servir la cola de envíos. |
| [karel.js](https://github.com/omegaup/karel.js) | La versión oficial de Karel utilizada por la Olimpiada Mexicana de Informática. |
| [omegajail](https://github.com/omegaup/omegajail) | Un mecanismo de ejecución segura que basado en contenedores de Linux y seccomp-bpf. Utiliza [minijail](https://android.googlesource.com/platform/external/minijail/+/master), escrito por el proyecto [Chromium](https://www.chromium.org). |
| [libinteractive](https://github.com/omegaup/libinteractive) | Una librería para hacer problemas interactivos fácilmente. |

## 🌐 Navegadores Soportados

Los navegadores oficialmente soportados son aquellos que soportan [ECMAScript 2015 (ES6)](https://caniuse.com/#feat=es6), e incluyen los siguientes:

| Navegador | Versión |
| :--- | :--- |
| [Chrome](https://www.google.com/chrome/) | 51 |
| [Firefox](http://mozilla.org/firefox/releases/) | 68 |
| [Edge](https://www.microsoft.com/edge) | 12 |
| [Safari](https://www.apple.com/safari/) | 12 |

Esto también incluye todos los navegadores basados en Blink / WebKit cuyas versiones sean compatibles con las de Chrome / Safari.

## 💻 Desarrollo Local

Para configurar el entorno de desarrollo localmente, consulta la [Guía de Configuración del Entorno de Desarrollo](frontend/www/docs/Development-Environment-Setup-Process.md).

### 🚀 Inicio Rápido

```bash
# Clonar con submódulos
git clone --recurse-submodules [https://github.com/TU_USUARIO/omegaup](https://github.com/TU_USUARIO/omegaup)
cd omegaup

# Si ya clonaste sin submódulos, inicialízalos:
git submodule update --init --recursive

# Instalar dependencias y ejecutar pruebas
yarn install
yarn test
📄 LicenciaBSD🇬🇧 EnglishomegaUp is a free educational platform that helps improve programming skills, used by tens of thousands of students and teachers in Latin America.See the Issue Assignment Workflow for how to self-assign issues, deadlines, and limits.💻 Local DevelopmentTo set up the development environment locally, see the Development Environment Setup Guide.🚀 Quick StartBash# Clone with submodules
git clone --recurse-submodules [https://github.com/YOURUSERNAME/omegaup](https://github.com/YOURUSERNAME/omegaup)
cd omegaup

# If you already cloned without submodules, initialize them:
git submodule update --init --recursive

# Install dependencies and run tests
yarn install
yarn test
📁 DirectoriesDirectories that are actively used in development.DirectoryDescriptionfrontend/server/src/ControllersBusiness logic that implements the omegaUp API.frontend/server/libsLibraries and props.frontend/server/src/DAOData Access Objects [DAO] and Value Objects [VO]. Classes used to represent database schemas and facilitate their consumption by controllers.frontend/templatesTemplates used to generate the HTML that is displayed to users. Also here are the internationalization files for English, Spanish and Portuguese.frontend/wwwThe complete contents of the website.🔗 The rest of the code is in other repositoriesRepositoryDescriptionquarkIt includes the grader code for rating issues and running the codes under minijail, as well as the service used on the cloud servers to serve the submission queue.karel.jsThe official version of Karel used by the Mexican Informatics Olympiad.omegajailA secure execution mechanism based on Linux containers and seccomp-bpf. It uses minijail, written by the Chromium project.libinteractiveA library to easily do interactive problems.🌐 Supported BrowsersOfficially supported browsers are those that support ECMAScript 2015 (ES6), and include the following:BrowserVersionChrome51Firefox68Edge12Safari12This also includes all Blink/WebKit-based browsers whose versions are compatible with Chrome/Safari.📄 LicenseBSD🇵🇹 PortuguêsomegaUp é uma plataforma educacional gratuita que ajuda a melhorar as habilidades de programação, usada por dezenas de milhares de estudantes e professores na América Latina.📁 DiretóriosDiretórios que são usados ativamente no desenvolvimento.DiretórioDescriçãofrontend/server/src/ControllersLógica de negócios que implementa a API omegaUp.frontend/server/libsBibliotecas e adereços.frontend/server/src/DAOObjetos de acesso a dados [DAO] e objetos de valor [VO]. Classes utilizadas para representar esquemas de banco de dados e facilitar seu consumo pelos controladores.frontend/templatesModelos usados para gerar o HTML que é exibido aos usuários. Aqui também estão os arquivos de internacionalização para inglês, espanhol e português.frontend/wwwO conteúdo completo do site.🔗 O resto do código está em outros repositóriosRepositórioDescriçãoquarkInclui o código do avaliador para avaliar problemas e executar os códigos no minijail, bem como o serviço usado nos servidores em nuvem para atender a fila de envio.karel.jsA versão oficial do Karel usada pela Olimpíada Mexicana de Informática.omegajailUm mecanismo de execução seguro baseado em contêineres Linux e seccomp-bpf. Ele usa minijail, escrito pelo projeto Chromium.libinteractiveUma biblioteca para resolver facilmente problemas interativos.🌐 Navegadores SuportadosOs navegadores oficialmente suportados são aqueles que suportam ECMAScript 2015 (ES6) e incluem o seguinte:NavegadorVersãoChrome51Firefox68Edge12Safari12Isso também inclui todos os navegadores baseados em Blink/WebKit cujas versões são compatíveis com Chrome/Safari.💻 Desenvolvimento LocalPara configurar o ambiente de desenvolvimento localmente, consulte o Guia de Configuração do Ambiente de Desenvolvimento.🚀 Início RápidoBash# Clonar com submódulos
git clone --recurse-submodules [https://github.com/SEUUSUARIO/omegaup](https://github.com/SEUUSUARIO/omegaup)
cd omegaup

# Se você já clonou sem submódulos, inicialize-os:
git submodule update --init --recursive

# Instalar dependências e executar testes
yarn install
yarn test
📄 LicençaBSD🇮🇹 ItalianoomegaUp è una piattaforma educativa gratuita che aiuta a migliorare le abilità nella programmazione, usata da decine di migliaia di studenti ed insegnanti in America Latina.📁 CartelleLe cartelle che sono attivamente utilizzate nello sviluppo.CartellaDescrizionefrontend/server/src/ControllersLogica di business che implementa le API di omegaUp.frontend/server/libsLibrerie e utility.frontend/server/src/DAOI Data Access Objects [DAO] e Value Objects [VO]. Classi utilizzate per rappresentare gli schemi del database e facilitare il loro utilizzo da parte dei controller.frontend/templatesModelli utilizzati per generare l'HTML visualizzato agli utenti. Sono anche presenti i file di internazionalizzazione per inglese, spagnolo e portoghese.frontend/wwwL'intero contenuto del sito web.🔗 Il resto del codice si trova in altre repository.RepositoryDescrizionequarkInclude il codice del grader per la valutazione dei problemi e l'esecuzione dei codici sotto minijail, così come il servizio utilizzato nei server cloud per gestire la coda delle sottomissioni.karel.jsLa versione ufficiale di Karel utilizzata dall'Olimpiade Messicana di Informatica.omegajailUn meccanismo di esecuzione sicura basato su container Linux e seccomp-bpf. Utilizza minijail, sviluppato dal progetto Chromium.libinteractiveUna libreria per creare facilmente problemi interattivi.🌐 Browser SupportatiI browser ufficialmente supportati sono quelli che supportano ECMAScript 2015 (ES6), e comprendono i seguenti:BrowserVersioneChrome51Firefox68Edge12Safari12Questo include anche tutti i browser basati su Blink/WebKit le cui versioni sono compatibili con quelle di Chrome/Safari.💻 Sviluppo LocalePer configurare l'ambiente di sviluppo localmente, consulta la Guida alla Configurazione dell'Ambiente di Sviluppo.🚀 Avvio RapidoBash# Clonare con i submodule
git clone --recurse-submodules [https://github.com/TUONOMEUTENTE/omegaup](https://github.com/TUONOMEUTENTE/omegaup)
cd omegaup

# Se hai già clonato senza submodule, inizializzali:
git submodule update --init --recursive

# Installare le dipendenze ed eseguire i test
yarn install
yarn test
📄 LicenzaBSD

# Arquitectura de software

omegaUp.com se diseño usando el modelo [MVC](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller). 

## Diagrama
```mermaid
flowchart TD
    WebApp[fa:fa-desktop Web app]
    CLI[fa:fa-terminal Other clients]
    
    subgraph Cloud[fa:fa-cloud Cloud Infrastructure]
        MySQL[(MySQL)]
        DAOs[DAOs]
        Controllers[Controllers/APIs]
        GitServer[GitServer]
        Grader[Grader]
        
        subgraph Runners
            Runner1[Runner 1]
            Runner2[Runner 2]
            Runner3[Runner 3]
        end
        
        Controllers --> GitServer
        Controllers --> DAOs
        DAOs --> MySQL
        Controllers --> Grader
        Grader --> MySQL
        Grader --> Runner1
        Grader --> Runner2
        Grader --> Runner3
    end
    
    WebApp --> Controllers
    CLI --> Controllers

```


## Tecnologías

The list of technologies we will use to build the application are as follows:

| Technology          | Purpose                | Version  |
| ------------------- | ---------------------- | -------- |
| MySql            | Database             | [6.0.9] |
| PHP              | Controladores        | [8.1]   |
| Python           | Cronjobs             | [3.0.1] |
| Typescript       | Frontend             | [2.4.1]  |
| VueJS            | Frontend             | [3.0.2] |
| Bootstrap4       | Frontend             | [4.0.1] |
| Go               | Grader               | [17.0.1] |

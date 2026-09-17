iniciar-programa
    define-nueva-instruccion gira-derecha como inicio
        repetir 3 veces inicio
            gira-izquierda;
        fin;
    fin;

    define-nueva-instruccion caminar como inicio
        si no-junto-a-zumbador entonces inicio
            si frente-libre entonces inicio
                avanza;
                caminar;
            fin sino inicio
                si izquierda-libre entonces inicio
                    gira-izquierda;
                    caminar;
                    gira-derecha;
                fin sino inicio
                    gira-derecha;
                    caminar;
                    gira-izquierda;
                fin;
            fin;
        fin;
    fin;

    inicia-ejecucion
        caminar;
    termina-ejecucion
finalizar-programa

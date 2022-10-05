#include "extremos.h"

// Main
//	bool esMenor(int i, int j)
//	void respuesta(int posMenor, int posMayor)

int menor,mayor;


void buscaExtremos(int n) {

    if(esMenor(n-1,n)){
        menor=n-1;
        mayor=n;

    }else{
    menor=n;
    mayor=n-1;
    }
	for(int l=1;l<= n-2;l++){
        if(esMenor(l,menor)){
            menor=l;

        }else{
            if(esMenor(mayor,l)){
                    mayor=l;


            }


        }

	}

	return respuesta(menor,mayor);

}
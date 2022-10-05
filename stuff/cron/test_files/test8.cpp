#include "extremos.h"

// Main
//	bool esMenor(int i, int j)
//	void respuesta(int posMenor, int posMayor)

void buscaExtremos(int n) {
    int menor,mayor,k;
    bool resultado;
    menor=1;
    mayor=1;
    for (k=1; k<n; k++){
        resultado=esMenor(menor,k+1);
        if (resultado==false){
            menor=k+1;
        }
    }
    for (k=1; k<n; k++){
        resultado=esMenor(mayor,k+1);
        if (resultado==true){
            mayor=k+1;
        }
    }

	return respuesta(menor,mayor);
}
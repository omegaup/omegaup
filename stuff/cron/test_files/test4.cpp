#include "extremos.h"

// Main
//	bool esMenor(int i, int j)
//	void respuesta(int posMenor, int posMayor)

void buscaExtremos(int n) {
	// FIXME
	int mayor = 1, menor = 1;
	for(int i = 2; i <= n; ++i){
        bool aux = esMenor(menor, i);
        if(!aux){
            menor = i;
        }else{
            bool aux2 = esMenor(mayor, i);
            if(aux2){
                mayor = i;
            }
        }
	}
	respuesta(menor, mayor);
}
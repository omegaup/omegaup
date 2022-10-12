#include "extremos.h"

// Main
//	bool esMenor(int i, int j)
//	void respuesta(int posMenor, int posMayor)

void buscaExtremos(int n) {
    int a=1, b=2, c=2, d=0;
    while(b != a){
        for(int i=2; i<=n; i++){
        if(esMenor(a, i)){
            a=i;
        }
        else b=a;
        }
    }
    a=1;
    while(d<n){
        for(int i=2; i<=n; i++){
        if(!esMenor(a, i)){
           a=i;
        }
        else c=a;
        }
        d = n;
    }
    respuesta(c, b);
}
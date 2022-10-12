#include "extremos.h"

// Main
//	bool esMenor(int i, int j)
//	void respuesta(int posMenor, int posMayor)


int dividir_menor(int a[], int tam)
{
    if (tam == 2){
        if ( esMenor(a[0], a[1]) ){return a[0];}
        return a[1];
    }

    int p1=0;
    int menor[tam];
    for (int i=1; i<tam; i++)
    {
        if ( esMenor(a[i], a[0]) )
        {
            menor[p1] = a[i];
            p1++;
        }
    }
    if (p1 == 0){return a[0];}
    dividir_menor(menor, p1);
}


int dividir_mayor(int a[], int tam)
{
    //se te paso poner que analizara los elementos del arreglo???
    if (tam == 2){
        if ( esMenor(a[0], a[1]) ){return a[1];}
        return a[0];
    }

    int p2=0;
    int mayor[tam];
    for (int i=1; i<tam; i++)
    {
        if ( !esMenor( a[i], a[0]) )
        {
            mayor[p2] = a[i];
            p2++;
        }
    }
    if (p2 == 0){return a[0];}
    dividir_mayor(mayor, p2);
}


void buscaExtremos(int n) {
    int p1=0, p2=0;
    int menor[n];
    int mayor[n];

	for (int i=2; i<=n; i++)
    {
        if (esMenor(i, 1)){
            menor[p1] = i;
            p1++;
        }
        else{
            mayor[p2] = i;
            p2++;
        }
    }
    int rMayor, rMenor;
    if (p1 == 0){rMenor = 1;}
    else {rMenor = dividir_menor(menor, p1);}

    if (p2 == 0){rMayor = 1;}
    else {rMayor = dividir_mayor(mayor, p2);}

    respuesta(rMenor, rMayor);
}
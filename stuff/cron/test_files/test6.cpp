#include "extremos.h"

// Main
//	bool esMenor(int i, int j)
//	void respuesta(int posMenor, int posMayor)

void buscaExtremos(int n) {
    int current_smallest = 1;
    int current_largest = 1;
    
    for(int i = 2; i <= n; i++)
    {
        if(esMenor(i,current_smallest))
        {
            current_smalles = i;
        }
        if(esMenor(current_largest, i))
        {
            current_largest = i;
        }
    }
    respuesta(current_smallest, current_largest);
	// FIXME
}
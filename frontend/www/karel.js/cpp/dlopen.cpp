#include <stdlib.h>

extern "C" void dlopen() {
	abort();
}

extern "C" void dlsym() {
	abort();
}

extern "C" void dlclose() {
	abort();
}

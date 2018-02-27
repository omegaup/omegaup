CC=g++
CFLAGS=-O2

.PHONY: all clean
all: normalizr

clean:
	@rm normalizr

normalizr: normalizr.cpp
	$(CC) -Wall $^ $(CFLAGS) -o $@

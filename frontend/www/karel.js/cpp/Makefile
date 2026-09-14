CFLAGS:=-Werror -Wall -fno-exceptions
LDFLAGS:=-static
CXXFLAGS:=-std=c++17
LLVM_CXXFLAGS:=$(shell llvm-config --cxxflags)
LLVM_LDFLAGS:=$(shell llvm-config --ldflags --system-libs --libs core --link-static)
BINS:=karel karel.js karel-asm.js

.PHONY: all
all: ${BINS}

karel: main.cpp karel.cpp util.cpp logging.cpp xml.cpp json.cpp
	g++ $^ -static -O2 ${CFLAGS} ${CXXFLAGS} -lexpat -o $@

karel2: main.cpp karel.cpp util.cpp logging.cpp xml.cpp json.cpp
	clang++-6.0 $^ -static -g ${CFLAGS} ${CXXFLAGS} -lexpat -o $@

karel.js: karel_wasm_main.cpp karel.cpp util.cpp logging.cpp json.cpp
	emcc -Oz $^ -s "BINARYEN_METHOD='native-wasm'" -s TOTAL_MEMORY=64MB -s WASM=1 -s EXPORTED_FUNCTIONS="['_malloc','_free']" ${CFLAGS} ${CXXFLAGS} -o $@

karel-asm.js: karel_wasm_main.cpp karel.cpp util.cpp logging.cpp json.cpp
	emcc -Oz $^ -s "BINARYEN_METHOD='asmjs'" -s TOTAL_MEMORY=64MB -s WASM=1 -s EXPORTED_FUNCTIONS="['_malloc','_free']" ${CFLAGS} ${CXXFLAGS} -o $@

kcl: kcl.cpp
	g++ $^ ${CFLAGS} ${CXXFLAGS} ${LLVM_CXXFLAGS} ${LDFLAGS} ${LLVM_LDFLAGS} -o $@

.PHONY: test
test: karel
	./test.sh

.PHONY: clean
clean:
	rm -f ${BINS}

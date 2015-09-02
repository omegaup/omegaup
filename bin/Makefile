CC=g++
CFLAGS=-O2

-include ../libinteractive/definitions.mk
-include ../backend/definitions.mk

.PHONY: all clean
all: normalizr libinteractive.jar runner.jar grader.jar

clean:
	@rm normalizr
	$(MAKE) -C ../libinteractive clean
	$(MAKE) -C ../backend clean

$(LIBINTERACTIVE_JAR): $(LIBINTERACTIVE_SOURCES)
	$(MAKE) -C ../libinteractive all

$(RUNNER_JAR) $(GRADER_JAR): $(BACKEND_SOURCES) $(LIBINTERACTIVE_JAR)
	$(MAKE) -C ../backend all
    
libinteractive.jar: $(LIBINTERACTIVE_JAR)
	cp $^ $@

runner.jar: $(RUNNER_JAR)
	cp $^ $@

grader.jar: $(GRADER_JAR)
	cp $^ $@

normalizr: normalizr.cpp
	$(CC) -Wall $^ $(CFLAGS) -o $@

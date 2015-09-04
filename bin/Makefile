CC=g++
CFLAGS=-O2

-include ../libinteractive/definitions.mk
-include ../backend/definitions.mk

.PHONY: all clean
all: normalizr runner.jar grader.jar

clean:
	@rm normalizr
	$(MAKE) -C ../libinteractive clean
	$(MAKE) -C ../backend clean

$(RUNNER_JAR) $(GRADER_JAR): $(BACKEND_SOURCES)
	$(MAKE) -C ../backend all

runner.jar: $(RUNNER_JAR)
	cp $^ $@

grader.jar: $(GRADER_JAR)
	cp $^ $@

normalizr: normalizr.cpp
	$(CC) -Wall $^ $(CFLAGS) -o $@

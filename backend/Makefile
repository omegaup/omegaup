SOURCES := $(/usr/bin/find -name *.scala)
SCALA_VERSION := 2.10
OMEGAUP_VERSION := 1.1
RUNNER_JAR := runner/target/scala-$(SCALA_VERSION)/proguard/runner_$(SCALA_VERSION)-$(OMEGAUP_VERSION).jar
GRADER_JAR := grader/target/scala-$(SCALA_VERSION)/proguard/grader_$(SCALA_VERSION)-$(OMEGAUP_VERSION).jar

all: ../bin/grader.jar ../bin/runner.jar

clean:
	@rm $(RUNNER_JAR) $(GRADER_JAR)

../bin/grader.jar: $(GRADER_JAR)
	cp $< $@

../bin/runner.jar: $(RUNNER_JAR)
	cp $< $@

$(GRADER_JAR): $(SOURCES)
	sbt proguard:proguard

$(RUNNER_JAR): $(SOURCES)
	sbt proguard:proguard

.PHONY: test
test: test-quark test-gitserver

.PHONY: test-quark
test-quark: .get-stamp
	go test -tags=static \
		github.com/omegaup/quark/...

.PHONY: test-gitserver
test-gitserver: .get-stamp
	go test -tags=static \
		github.com/omegaup/gitserver/...

.get-stamp:
	go get -t -tags=static \
		github.com/omegaup/go-base \
		github.com/omegaup/quark \
		github.com/omegaup/githttp \
		github.com/omegaup/gitserver
	touch $@


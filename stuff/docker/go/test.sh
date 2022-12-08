#!/bin/bash

# Running this will open a shell where the sources for quark is
# installed. Useful to develop quark in a jiffy. Run all tests with:
#
#   make
#
# Or only the quark tests with
#
#   make test-quark

DIR="$(realpath "$(git rev-parse --show-toplevel)")/stuff/docker/"

if [[ ! -d "${DIR}/go/go-base" ]]; then
	git clone https://github.com/omegaup/go-base.git "${DIR}/go/go-base"
fi
if [[ ! -d "${DIR}/go/quark" ]]; then
	git clone https://github.com/omegaup/quark.git "${DIR}/go/quark"
fi
if [[ ! -d "${DIR}/go/githttp" ]]; then
	git clone https://github.com/omegaup/githttp.git "${DIR}/go/githttp"
fi
if [[ ! -d "${DIR}/go/gitserver" ]]; then
	git clone https://github.com/omegaup/gitserver.git "${DIR}/go/gitserver"
fi

DOCKER_BUILDKIT=1 docker build \
	--target=base-builder \
	--tag=omegaup/local-backend-base-builder \
	--file="${DIR}/Dockerfile.local-backend" \
	"${DIR}"
DOCKER_BUILDKIT=1 docker build \
	--tag=omegaup/local-backend-test \
	--file="${DIR}/Dockerfile.local-backend-test" \
	"${DIR}"
DOCKER_BUILDKIT=1 docker run \
	--rm \
	--interactive \
	--tty \
	--name=dev-backend-test-local \
	--mount "type=bind,source=${DIR}/go/go-base,target=/home/ubuntu/go/omegaup/go-base" \
	--mount "type=bind,source=${DIR}/go/quark,target=/home/ubuntu/go/omegaup/quark" \
	--mount "type=bind,source=${DIR}/go/githttp,target=/home/ubuntu/go/omegaup/githttp" \
	--mount "type=bind,source=${DIR}/go/gitserver,target=/home/ubuntu/go/omegaup/gitserver" \
	omegaup/local-backend-test

#!/bin/bash

DIR="$(realpath "$(dirname "$(dirname "${0}")")")"

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

docker build \
	--target=base-builder \
	--tag=omegaup/local-backend-base-builder \
	--file="${DIR}/Dockerfile.local-backend" \
	"${DIR}"
docker build \
	--tag=omegaup/local-backend-test \
	--file="${DIR}/Dockerfile.local-backend-test" \
	"${DIR}"
docker run \
	--rm \
	--interactive \
	--tty \
	--name=dev-backend-test-local \
	--mount "type=bind,source=${DIR}/go/go-base,target=/home/ubuntu/go/omegaup/go-base" \
	--mount "type=bind,source=${DIR}/go/quark,target=/home/ubuntu/go/omegaup/quark" \
	--mount "type=bind,source=${DIR}/go/githttp,target=/home/ubuntu/go/omegaup/githttp" \
	--mount "type=bind,source=${DIR}/go/gitserver,target=/home/ubuntu/go/omegaup/gitserver" \
	omegaup/local-backend-test

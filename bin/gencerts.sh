#!/bin/bash

OPENSSL=/usr/bin/openssl
KEYTOOL=/usr/bin/keytool

if [ "bin" == "$(basename `pwd`)" ]; then
	cd ..
fi

if [ -f grader/omegaup.jks ]; then rm grader/omegaup.jks; fi
if [ -f runner/omegaup.jks ]; then rm runner/omegaup.jks; fi
if [ -d ssl ]; then rm ssl/*; else mkdir ssl; fi

$OPENSSL genrsa -out ssl/omegaup-ca.key 4096
$OPENSSL req -new -subj '/C=MX/CN=OmegaUp Certificate Authority' -x509 -days 3650 -key ssl/omegaup-ca.key -out ssl/omegaup-ca.crt

$OPENSSL genrsa -out ssl/grader.key 1024
$OPENSSL req -new -subj "/C=MX/CN=OmegaUp Grader" -key ssl/grader.key -out ssl/grader.csr
$OPENSSL x509 -req -days 3650 -in ssl/grader.csr -CA ssl/omegaup-ca.crt -CAkey ssl/omegaup-ca.key -set_serial 1 -out ssl/grader.crt
rm ssl/grader.csr

$OPENSSL pkcs12 -export -in ssl/grader.crt -inkey ssl/grader.key -name "OmegaUp Grader" -certfile ssl/omegaup-ca.crt -caname "OmegaUp Certificate Authority" -password pass:omegaup -out ssl/grader.p12
$KEYTOOL -importkeystore -srckeystore ssl/grader.p12 -srcstoretype pkcs12 -srcstorepass omegaup -srcalias "OmegaUp Grader" -destkeystore grader/omegaup.jks -deststoretype jks -deststorepass omegaup
$KEYTOOL -importcert -alias "OmegaUp Certificate Authority" -noprompt -trustcacerts -keystore grader/omegaup.jks -storepass omegaup -file ssl/omegaup-ca.crt
rm ssl/grader.p12 ssl/grader.key ssl/grader.crt

$OPENSSL genrsa -out ssl/runner.key 1024
$OPENSSL req -new -subj "/C=MX/CN=OmegaUp Runner" -key ssl/runner.key -out ssl/runner.csr
$OPENSSL x509 -req -days 3650 -in ssl/runner.csr -CA ssl/omegaup-ca.crt -CAkey ssl/omegaup-ca.key -set_serial 2 -out ssl/runner.crt
rm ssl/runner.csr

$OPENSSL pkcs12 -export -in ssl/runner.crt -inkey ssl/runner.key -name "OmegaUp Runner" -certfile ssl/omegaup-ca.crt -caname "OmegaUp Certificate Authority" -password pass:omegaup -out ssl/runner.p12
$KEYTOOL -importkeystore -srckeystore ssl/runner.p12 -srcstoretype pkcs12 -srcstorepass omegaup -srcalias "OmegaUp Runner" -destkeystore runner/omegaup.jks -deststoretype jks -deststorepass omegaup
$KEYTOOL -importcert -alias "OmegaUp Certificate Authority" -noprompt -trustcacerts -keystore runner/omegaup.jks -storepass omegaup -file ssl/omegaup-ca.crt
rm ssl/runner.p12 ssl/runner.key ssl/runner.crt

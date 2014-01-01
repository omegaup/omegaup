#!/bin/bash

if [ "bin" == "$(basename `pwd`)" ]; then
	cd ..
fi

if [ -f backend/grader/omegaup.jks ]; then rm backend/grader/omegaup.jks; fi
if [ -f backend/runner/omegaup.jks ]; then rm backend/runner/omegaup.jks; fi
if [ -d ssl ]; then rm ssl/*; else mkdir ssl; fi

/usr/bin/openssl genrsa -out ssl/omegaup-ca.key 4096
/usr/bin/openssl req -new -subj '/C=MX/CN=OmegaUp Certificate Authority' -x509 -days 3650 -key ssl/omegaup-ca.key -out ssl/omegaup-ca.crt

/usr/bin/openssl genrsa -out ssl/grader.key 1024
/usr/bin/openssl req -new -subj "/C=MX/CN=OmegaUp Grader" -key ssl/grader.key -out ssl/grader.csr
/usr/bin/openssl x509 -req -days 3650 -in ssl/grader.csr -CA ssl/omegaup-ca.crt -CAkey ssl/omegaup-ca.key -set_serial 1 -out ssl/grader.crt
rm ssl/grader.csr

/usr/bin/openssl pkcs12 -export -in ssl/grader.crt -inkey ssl/grader.key -name "OmegaUp Grader" -certfile ssl/omegaup-ca.crt -caname "OmegaUp Certificate Authority" -password pass:omegaup -out ssl/grader.p12
/usr/bin/keytool -importkeystore -srckeystore ssl/grader.p12 -srcstoretype pkcs12 -srcstorepass omegaup -srcalias "OmegaUp Grader" -destkeystore backend/grader/omegaup.jks -deststoretype jks -deststorepass omegaup
/usr/bin/keytool -importcert -alias "OmegaUp Certificate Authority" -noprompt -trustcacerts -keystore backend/grader/omegaup.jks -storepass omegaup -file ssl/omegaup-ca.crt
rm ssl/grader.p12 ssl/grader.key ssl/grader.crt

/usr/bin/openssl genrsa -out ssl/runner.key 1024
/usr/bin/openssl req -new -subj "/C=MX/CN=OmegaUp Runner" -key ssl/runner.key -out ssl/runner.csr
/usr/bin/openssl x509 -req -days 3650 -in ssl/runner.csr -CA ssl/omegaup-ca.crt -CAkey ssl/omegaup-ca.key -set_serial 2 -out ssl/runner.crt
rm ssl/runner.csr

/usr/bin/openssl pkcs12 -export -in ssl/runner.crt -inkey ssl/runner.key -name "OmegaUp Runner" -certfile ssl/omegaup-ca.crt -caname "OmegaUp Certificate Authority" -password pass:omegaup -out ssl/runner.p12
/usr/bin/keytool -importkeystore -srckeystore ssl/runner.p12 -srcstoretype pkcs12 -srcstorepass omegaup -srcalias "OmegaUp Runner" -destkeystore backend/runner/omegaup.jks -deststoretype jks -deststorepass omegaup
/usr/bin/keytool -importcert -alias "OmegaUp Certificate Authority" -noprompt -trustcacerts -keystore backend/runner/omegaup.jks -storepass omegaup -file ssl/omegaup-ca.crt
rm ssl/runner.p12 ssl/runner.key ssl/runner.crt

/usr/bin/openssl genrsa -out ssl/frontend.key 1024
/usr/bin/openssl req -new -subj "/C=MX/CN=OmegaUp Frontend" -key ssl/frontend.key -out ssl/frontend.csr
/usr/bin/openssl x509 -req -days 3650 -in ssl/frontend.csr -CA ssl/omegaup-ca.crt -CAkey ssl/omegaup-ca.key -set_serial 3 -out ssl/frontend.crt
rm ssl/frontend.csr

cat ssl/frontend.key ssl/frontend.crt ssl/omegaup-ca.crt > frontend/omegaup.pem
rm ssl/frontend.key ssl/frontend.crt

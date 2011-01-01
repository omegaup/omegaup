#!/bin/bash

OPENSSL=/usr/bin/openssl
KEYTOOL=/usr/bin/keytool

if [ "bin" == "$(basename `pwd`)" ]; then
	cd ..
fi

if [ -f grader/openjuan.jks ]; then rm grader/openjuan.jks; fi
if [ -f runner/openjuan.jks ]; then rm runner/openjuan.jks; fi
if [ -d ssl ]; then rm ssl/*; else mkdir ssl; fi

$OPENSSL genrsa -out ssl/openjuan-ca.key 4096
$OPENSSL req -new -subj '/C=MX/CN=OpenJuan Certificate Authority' -x509 -days 3650 -key ssl/openjuan-ca.key -out ssl/openjuan-ca.crt

$OPENSSL genrsa -out ssl/grader.key 1024
$OPENSSL req -new -subj "/C=MX/CN=OpenJuan Grader" -key ssl/grader.key -out ssl/grader.csr
$OPENSSL x509 -req -days 3650 -in ssl/grader.csr -CA ssl/openjuan-ca.crt -CAkey ssl/openjuan-ca.key -set_serial 1 -out ssl/grader.crt
rm ssl/grader.csr

$OPENSSL pkcs12 -export -in ssl/grader.crt -inkey ssl/grader.key -name "OpenJuan Grader" -certfile ssl/openjuan-ca.crt -caname "OpenJuan Certificate Authority" -password pass:openjuan -out ssl/grader.p12
$KEYTOOL -importkeystore -srckeystore ssl/grader.p12 -srcstoretype pkcs12 -srcstorepass openjuan -srcalias "OpenJuan Grader" -destkeystore grader/openjuan.jks -deststoretype jks -deststorepass openjuan
$KEYTOOL -importcert -alias "OpenJuan Certificate Authority" -noprompt -trustcacerts -keystore grader/openjuan.jks -storepass openjuan -file ssl/openjuan-ca.crt
rm ssl/grader.p12 ssl/grader.key ssl/grader.crt

$OPENSSL genrsa -out ssl/runner.key 1024
$OPENSSL req -new -subj "/C=MX/CN=OpenJuan Runner" -key ssl/runner.key -out ssl/runner.csr
$OPENSSL x509 -req -days 3650 -in ssl/runner.csr -CA ssl/openjuan-ca.crt -CAkey ssl/openjuan-ca.key -set_serial 2 -out ssl/runner.crt
rm ssl/runner.csr

$OPENSSL pkcs12 -export -in ssl/runner.crt -inkey ssl/runner.key -name "OpenJuan Runner" -certfile ssl/openjuan-ca.crt -caname "OpenJuan Certificate Authority" -password pass:openjuan -out ssl/runner.p12
$KEYTOOL -importkeystore -srckeystore ssl/runner.p12 -srcstoretype pkcs12 -srcstorepass openjuan -srcalias "OpenJuan Runner" -destkeystore runner/openjuan.jks -deststoretype jks -deststorepass openjuan
$KEYTOOL -importcert -alias "OpenJuan Certificate Authority" -noprompt -trustcacerts -keystore runner/openjuan.jks -storepass openjuan -file ssl/openjuan-ca.crt
rm ssl/runner.p12 ssl/runner.key ssl/runner.crt

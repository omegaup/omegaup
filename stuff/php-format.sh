#!/bin/sh -e

# Validate only, does not make changes.
VALIDATE=0

for i in "$@"; do
	case $i in
		--validate)
			VALIDATE=1
			;;
	esac
	shift
done

if [ $VALIDATE -eq 1 ]; then
	# TODO(lhchavez): Remove the -n to also enforce being warning-free.
	COMMAND='phpcs -n -s'
else
	COMMAND=phpcbf
fi

${COMMAND} --extensions=php --standard=stuff/omegaup-standard.xml --encoding=utf-8 --ignore=frontend/server/libs/dao/base/,frontend/server/libs/dao/Estructura.php,frontend/server/libs/adodb/,frontend/server/libs/log4php/,frontend/server/libs/google-api-php-client/,frontend/server/libs/facebook-php-sdk/,frontend/server/libs/log4php/,frontend/server/libs/Mailchimp/,frontend/server/libs/Markdown/,frontend/server/libs/phpmailer/,frontend/server/libs/smarty/,frontend/server/libs/PasswordHash.php,frontend/server/libs/ZipStream.php frontend

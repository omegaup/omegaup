#!/bin/sh -e

# Validate only, does not make changes.
VALIDATE=0

FILTERS='--include=*.css --include=*.js --include=*.php --include=*.sql --include=*.tpl --exclude-dir=facebook-php-sdk --exclude-dir=Markdown --exclude-dir=google-api-php-client --exclude-dir=smarty --exclude-dir adodb --exclude-dir phpmailer --exclude-dir Mailchimp --exclude-dir=base --exclude-dir=log4php --exclude-dir=mathjax --exclude-dir=*.git --exclude-dir=pagedown --exclude-dir=karel --exclude=jquery* --exclude=bootstrap* --exclude=codemirror*'

for i in "$@"; do
	case $i in
		--validate)
			VALIDATE=1
			;;
	esac
	shift
done

# Avoid trailing whitespace
VIOLATIONS=`grep -rl ${FILTERS} '\s\+$' frontend || true`
if [ ! -z "$VIOLATIONS" ]; then
	echo "Files have trailing whitespace: $VIOLATIONS" >&2
	if [ $VALIDATE != 1 ]; then
		/bin/sed -i -e 's/\s*$//' $VIOLATIONS
	fi
fi

# Avoid two consecutive empty lines
VIOLATIONS=`grep -Przl ${FILTERS} '(?s)\n\n\n' frontend || true`
if [ ! -z "$VIOLATIONS" ]; then
	echo "Files have consecutive empty lines: $VIOLATIONS" >&2
	if [ $VALIDATE != 1 ]; then
		/usr/bin/perl -i -0pe 's/\n\n\n+/\n\n/g' $VIOLATIONS
	fi
fi

# Avoid an empty line after an opening brace
VIOLATIONS=`grep -Przl ${FILTERS} '(?s){\n\n' frontend || true`
if [ ! -z "$VIOLATIONS" ]; then
	echo "Files have an empty line after an opening brace: $VIOLATIONS" >&2
	if [ $VALIDATE != 1 ]; then
		/usr/bin/perl -i -0pe 's/{\n\n+/{\n/g' $VIOLATIONS
	fi
fi

# Avoid an empty line before a closing brace
VIOLATIONS=`grep -Przl ${FILTERS} '(?s)\n\n\s*}' frontend || true`
if [ ! -z "$VIOLATIONS" ]; then
	echo "Files have an empty line after an opening brace: $VIOLATIONS" >&2
	if [ $VALIDATE != 1 ]; then
		/usr/bin/perl -i -0pe 's/\n\n+(\s*})/\n\1/g' $VIOLATIONS
	fi
fi

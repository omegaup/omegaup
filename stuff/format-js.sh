#!/bin/bash

git ls-tree -r HEAD | grep ' blob .*frontend/www/\(js\|ux\)/.*js$' | grep -v third_party | grep -v 'js/omegaup/lang' | sed -e 's/.*\t//' | xargs clang-format-3.7 -i --style=file
git ls-tree -r HEAD | grep ' blob .*frontend/www/\(js\|ux\)/.*js$' | grep -v third_party | grep -v 'js/omegaup/lang' | sed -e 's/.*\t//' | xargs ~/.local/bin/fixjsstyle --strict

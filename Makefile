.PHONY: empty
empty:

.PHONY: model
model:
	php script/ModelMakeShell.php

.PHONY: model-all
model-all:
	php script/AllModelMakeShell.php

.PHONY: test
test:
	cd Test; php TestRunner.php

.PHONY: init
init:
	php -r "copy('https://getcomposer.org/installer', 'composer.php');"
	php composer.php
	rm composer.php
	composer install
	npm install
	npm run build

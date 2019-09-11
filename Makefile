.PHONY: empty
empty:

.PHONY: model
model:
	gcc -o script/model_creator.out script/model_creator.c
	script/model_creator.out


.PHONY: test
test:
	cd Test; php TestRunner.php

.PHONY: init
init:
	php -r "copy('https://getcomposer.org/installer', 'composer.php');"
	php composer.php
	rm composer.php
	composer install
	npm install --save-dev \
		webpack \
		webpack-cli \
		node-sass \
		sass-loader \
		css-loader \
		npm-watch \
		vue
	npm run build


help:
	cat ./System/NotClass/Script/help

model:
	gcc -o ./System/NotClass/Script/model_creator.out \
	./System/NotClass/Script/model_creator.c
	./System/NotClass/Script/model_creator.out

init:
	gcc -o ./System/NotClass/Script/model_creator.out \
	./System/NotClass/Script/model_creator.c

test:
	cd Test; php TestRunner.php

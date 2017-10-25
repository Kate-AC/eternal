help:
	cat ./Eternal/NotClass/Script/help

model:
	gcc -o ./Eternal/NotClass/Script/model_creator.out \
	./Eternal/NotClass/Script/model_creator.c
	./Eternal/NotClass/Script/model_creator.out

init:
	gcc -o ./Eternal/NotClass/Script/model_creator.out \
	./Eternal/NotClass/Script/model_creator.c

test:
	cd Eternal/Test; php ./TestRunner.php

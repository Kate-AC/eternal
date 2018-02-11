#include "stdio.h"
#include "string.h"

#define MAX_NAMESPACE 256
#define MAX_BUFFER 512

void main()
{
	printf("/******************************************************/\n");
	printf("モデルのエンティティとスケルトンを生成します。\n");
	printf("存在するテーブルに対応する名前空間名を入力してください。\n");
	printf("フォルダは自動生成されます。(例: App\\Model\\Fuga\\Hoge)\n");
	printf("/******************************************************/\n");
	printf("> ");

	char namespace[MAX_NAMESPACE] = {};
	char buffer[MAX_BUFFER] = {};
	char command[] = "php ./Eternal/NotClass/Script/ModelCreatorScript.php  ";
	int i = 0;
	int j = 0;

	fgets(namespace, MAX_NAMESPACE, stdin);
	fflush(stdin);

	//バックスラッシュをエスケープする
	for (i = 0; i < MAX_BUFFER; i++) {
		buffer[j] = namespace[i];
		if ('\\' == namespace[i]) {
			j++;
			buffer[j] = '\\';
		}
		j++;
	}

	strcat(command, buffer);

	if (-1 == system(command)) {
		printf("ERROR: コマンドの実行に失敗しました。");
		return;
	}

	return;
}

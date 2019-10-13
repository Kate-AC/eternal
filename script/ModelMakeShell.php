<?php

require_once('./config.php');
require_once('./use.php');

class ModelMakeShell
{
    private $container;

    private $namespace = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function input()
    {
        if (null !== $this->namespace) {
            $modelCreator = $this->container->get('System\Util\ModelCreator');
            try {
                $modelCreator->initialize($this->namespace);
            } catch (SystemException $e) {
                echo sprintf('ERROR: %s', $e->getMessage()) . PHP_EOL;
                $this->namespace = null;
                $this->input();
            } catch (\Exception $e) {
                echo sprintf('ERROR: %s', $e->getMessage()) . PHP_EOL;
                $this->namespace = null;
                $this->input();
            }

            $modelCreator->makeDirectory();

            $message = sprintf('%sモデルの', $modelCreator->getModelName());
            if (false === $modelCreator->existEntity()) {
                $modelCreator->createEntity();
                $message .= 'Entityと';
            }
            $modelCreator->createSkeleton();
            $message .= 'Skeletonを生成しました';

            echo $message . PHP_EOL;
            exit;
        } else {
            echo sprintf("%s\n%s\n%s\n%s\n%s\n",
                '/******************************************************/',
                'モデルのエンティティとスケルトンを生成します。',
                '存在するテーブルに対応する名前空間名を入力してください。',
                'フォルダは自動生成されます。(例: App\\Model\\Fuga\\Hoge)',
                '/******************************************************/'
            );

            $this->namespace = trim(fgets(STDIN));

            if (null === $this->namespace) {
                echo 'ERROR: 名前空間が指定されていません。' . PHP_EOL;
            }

            $this->input();
        }
    }
}

(new ModelMakeShell($container))->input();

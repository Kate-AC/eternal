<?php

require_once('./config.php');
require_once('./use.php');

class AllModelMakeShell
{
    private $container;

    private $namespace = null;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function input()
    {
        $str = $this->container->get('System\Util\Str');
        $modelCreator = $this->container->get('System\Util\ModelCreator');
        $tableList = $modelCreator->getTableList();

        foreach ($tableList as $table) {
            $tableName = array_shift($table);
            $modelCreator->initialize('App\\Model\\' . ucfirst($str::snakeToCamel($tableName)));
            $modelCreator->makeDirectory();
            $message = sprintf('%sモデルの', $modelCreator->getModelName());
            if (false === $modelCreator->existEntity()) {
                $modelCreator->createEntity();
                $message .= 'Entityと';
            }
            $modelCreator->createSkeleton();
            $message .= 'Skeletonを生成しました';

            echo $message . PHP_EOL;
        }
    }
}
(new AllModelMakeShell($container))->input();

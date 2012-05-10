<?php

class StateMachineBehaviorQueryBuilderModifier
{
    /**
     * @var StateMachineBehavior
     */
    private $behavior;

    public function __construct(Behavior $behavior)
    {
        $this->behavior = $behavior;
    }

    protected function getColumnFilter($columnName)
    {
        return 'filterBy' . $this->behavior->getColumnForParameter($columnName)->getPhpName();
    }

    protected function getQueryClassName($builder)
    {
        return $builder->getStubQueryBuilder()->getClassname();
    }
}

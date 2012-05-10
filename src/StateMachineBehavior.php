<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StateMachineBehavior extends Behavior
{
	/**
	 * Default state column name
	 */
	const DEFAULT_STATE_COLUMN = 'state';

	/**
	 * @var array
	 */
	protected $parameters = array(
		'states'         => array(),
		'initial_state'  => null,
		'transition'     => array(),
		'state_column'   => self::DEFAULT_STATE_COLUMN,
	);

	/**
	 * @var array
	 */
	protected $states = array();

	/**
	 * @var array
	 */
	protected $humanizedStates = array();

    /**
     * @var StateMachineBehaviorObjectBuilderModifier
     */
    protected $objectBuilderModifier;

    /**
     * @var StateMachineBehaviorQueryBuilderModifier
     */
    protected $queryBuilderModifier;

    /**
     * {@inheritdoc}
     */
    public function modifyTable()
    {
        // add the 'is_published' column
        if (!$this->getTable()->containsColumn($this->getParameter('state_column'))) {
            $this->getTable()->addColumn(array(
                'name'          => $this->getParameter('state_column'),
                'type'          => 'INTEGER',
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectBuilderModifier()
    {
        if (null === $this->objectBuilderModifier) {
            $this->objectBuilderModifier = new StateMachineBehaviorObjectBuilderModifier($this);
        }

        return $this->objectBuilderModifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getQueryBuilderModifier()
    {
        if (null === $this->queryBuilderModifier) {
            $this->queryBuilderModifier = new StateMachineBehaviorQueryBuilderModifier($this);
        }

        return $this->queryBuilderModifier;
    }

	public function getStates()
	{
		if (null === $this->states) {
			foreach (explode(',', $this->getParameter('states')) as $state) {
				$this->states[] = strtolower(trim($state));
			}
		}

		return $this->states;
	}

	public function getHumanizedStates()
	{
		if (null === $this->humanizedStates) {
			foreach ($this->getStates() as $state) {
				$this->humanizedStates[$state] = $this->humanize($state);
			}
		}

		return $this->humanizedStates;
	}

	public function getTransitions()
	{
		return array();
	}

	public function getExceptionClass()
	{
		return 'LogicException';
	}

	protected function humanize($string)
	{
		return $string;
	}
}

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
    protected $states;

    /**
     * @var array
     */
    protected $transitions;

    /**
     * @var array
     */
    protected $symbols;

    /**
     * @var array
     */
    protected $humanizedStates;

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
    public function addParameter($attribute)
    {
        if ('transition' === $attribute['name']) {
            $values = explode('|', $attribute['value']);

            if (1 < count($values)) {
                $this->parameters['transition'] = $values;
            } else {
                $this->parameters['transition'][] = $attribute['value'];
            }
        } else {
            parent::addParameter($attribute);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        $parameters  = parent::getParameters();
        $parameters['transition'] = implode($parameters['transition'], '|');

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyTable()
    {
        $states       = $this->getStates();
        $defaultValue = array_search($this->getInitialState(), $states);


        // add the 'is_published' column
        if (!$this->getTable()->containsColumn($this->getParameter('state_column'))) {
            $this->getTable()->addColumn(array(
                'name'          => $this->getParameter('state_column'),
                'type'          => 'INTEGER',
                'defaultValue'  => $defaultValue,
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
            $states = array();
            foreach (explode(',', $this->getParameter('states')) as $state) {
                $states[] = strtolower(trim($state));
            }

            $this->states = $states;
        }

        return $this->states;
    }

    public function getHumanizedStates()
    {
        if (null === $this->humanizedStates) {
            foreach ($this->getStates() as $state) {
                $this->humanizedStates[$state] = $this->humanize($state);
            }
        }

        return $this->humanizedStates;
    }

    public function getTransitions()
    {
        if (null === $this->transitions) {
            foreach ($this->getParameter('transition') as $transition) {
                if (preg_match('#(?P<from>\w+) to (?P<to>\w+) with (?P<symbol>\w+)#', $transition, $matches)) {
                    $this->transitions[] = array(
                        'from'      => strtolower($matches['from']),
                        'to'        => strtolower($matches['to']),
                        'symbol'    => strtolower($matches['symbol']),
                    );
                }
            }
        }

        return $this->transitions;
    }

    public function getSymbols()
    {
        if (null === $this->symbols) {
            $symbols = array();
            foreach ($this->getTransitions() as $transition) {
                $symbols[] = $transition['symbol'];
            }

            $symbols = array_unique($symbols);
            sort($symbols, SORT_STRING);
            $this->symbols = $symbols;
        }

        return $this->symbols;
    }

    public function getTransitionsBySymbol($symbol)
    {
        return array_filter($this->getTransitions(), function ($transition) use ($symbol) {
            return $symbol === $transition['symbol'];
        });
    }

    public function getAntecedentStates($symbol)
    {
        return array_map(function ($transition) {
            return $transition['from'];
        }, $this->getTransitionsBySymbol($symbol));
    }

    public function getStateForSymbol($symbol)
    {
        $states = array_unique(array_map(function ($transition) {
            return $transition['to'];
        }, $this->getTransitionsBySymbol($symbol)));

        if (1 !== count($states)) {
            throw new LogicException(
                sprintf('There should be only one state for symbol "%s", got: %s', $symbol, var_export($states, true))
            );
        }

        return current($states);
    }

    public function getSymbolForState($state)
    {
        $transitions = array_filter($this->getTransitions(), function ($transition) use ($state) {
            return $state === $transition['to'];
        });
        $symbols = array_map(function ($transition) {
            return $transition['symbol'];
        }, $transitions);

        return current($symbols) ?: null;
    }

    public function getExceptionClass()
    {
        return 'LogicException';
    }

    public function getInitialState()
    {
        return strtolower($this->getParameter('initial_state'));
    }

    public function camelize($string)
    {
        return ucfirst(str_replace(' ', '', ucwords(strtr($string, '_-', '  '))));
    }

    public function humanize($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }
}

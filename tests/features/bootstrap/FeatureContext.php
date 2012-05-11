<?php

use Behat\Behat\Context\ClosuredContextInterface,
Behat\Behat\Context\TranslatedContextInterface,
Behat\Behat\Context\BehatContext,
Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

require_once __DIR__ . '/../../bootstrap.php';
require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    private $connection;

    private $instance;

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param   array   $parameters     context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
    }

    /**
     * @Given /^the following XML schema:$/
     */
    public function theFollowingXmlSchema(PyStringNode $schema)
    {
        $builder = new PropelQuickBuilder();
        $config  = $builder->getConfig();
        $config->setBuildProperty('behavior.state_machine.class', __DIR__ . '/../../../src/StateMachineBehavior');
        $builder->setConfig($config);
        $builder->setSchema($schema);

        $this->connection = $builder->build();
    }

    /**
     * @Given /^I want to manage a "([^"]*)"$/
     * @Given /^I want to manage an "([^"]*)"$/
     */
    public function iWantToManageA($className)
    {
        $this->instance = new $className();
    }

    /**
     * @Given /^Its default state is "([^"]*)"$/
     */
    public function itsDefaultStateIs($state)
    {
        $this->iShouldGetAnState($state);
    }

    /**
     * @When /^I "([^"]*)" it$/
     */
    public function iIt($symbolMethod)
    {
        $this->instance->$symbolMethod();
    }

    /**
     * @Then /^I should get a "([^"]*)" state$/
     * @Then /^I should get an "([^"]*)" state$/
     */
    public function iShouldGetAnState($state)
    {
        $state = ucwords(strtolower($state));
        assertEquals($state, $this->instance->getHumanizedState());
    }

    /**
     * @Given /^I should be able to "([^"]*)" it$/
     */
    public function iShouldBeAbleToIt($symbolMethod)
    {
        $canner = 'can' . ucfirst($symbolMethod);
        assertTrue($this->instance->$canner());
    }

    /**
     * @Given /^I should not be able to "([^"]*)" it$/
     * @Given /^I should not be able to "([^"]*)" it again$/
     */
    public function iShouldNotBeAbleToIt($symbolMethod)
    {
        $canner = 'can' . ucfirst($symbolMethod);
        assertFalse($this->instance->$canner());
    }
}

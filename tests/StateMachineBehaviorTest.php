<?php

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StateMachineBehaviorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('Post')) {
            $schema = <<<EOF
<database name="state_machine_behavior" defaultIdMethod="native">
    <table name="post">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="draft, unpublished, published" />

            <parameter name="initial_state" value="draft" />

            <parameter name="transition" value="draft to published with publish" />
            <parameter name="transition" value="published to unpublished with unpublish" />
            <parameter name="transition" value="unpublished to published with publish" />
        </behavior>
    </table>
</database>
EOF;
            $builder = new PropelQuickBuilder();
            $config  = $builder->getConfig();
            $config->setBuildProperty('behavior.state_machine.class', '../src/StateMachineBehavior');
            $builder->setConfig($config);
            $builder->setSchema($schema);

            $builder->build();
        }
    }

    public function testObjectMethods()
    {
        $this->assertTrue(method_exists('Post', 'isDraft'));
        $this->assertTrue(method_exists('Post', 'isUnpublished'));
        $this->assertTrue(method_exists('Post', 'isPublished'));
    }
}

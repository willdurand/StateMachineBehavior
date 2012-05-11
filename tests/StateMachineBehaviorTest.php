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

        $this->assertTrue(method_exists('Post', 'canPublish'));
        $this->assertTrue(method_exists('Post', 'canUnpublish'));

        $this->assertTrue(method_exists('Post', 'publish'));
        $this->assertTrue(method_exists('Post', 'unpublish'));

        $this->assertTrue(method_exists('Post', 'prePublish'));
        $this->assertTrue(method_exists('Post', 'onPublish'));
        $this->assertTrue(method_exists('Post', 'postPublish'));

        $this->assertTrue(method_exists('Post', 'preUnpublish'));
        $this->assertTrue(method_exists('Post', 'onUnpublish'));
        $this->assertTrue(method_exists('Post', 'postUnpublish'));

        $this->assertTrue(method_exists('Post', 'getAvailableStates'));
        $this->assertTrue(method_exists('Post', 'getState'));

        $this->assertTrue(defined('Post::STATE_DRAFT'));
        $this->assertTrue(defined('Post::STATE_PUBLISHED'));
        $this->assertTrue(defined('Post::STATE_UNPUBLISHED'));
    }

    public function testInitialState()
    {
        $post = new Post();
        $this->assertTrue($post->isDraft());
    }

    public function testGetState()
    {
        $post = new Post();
        $this->assertEquals(Post::STATE_DRAFT, $post->getState());
    }

    public function testGetAvailableStates()
    {
        $post = new Post();
        $expected = array(
            Post::STATE_DRAFT,
            Post::STATE_PUBLISHED,
            Post::STATE_UNPUBLISHED,
        );

        $this->assertEquals($expected, $post->getAvailableStates());
    }

    public function testIssersDefaultValues()
    {
        $post = new Post();

        $this->assertTrue($post->isDraft());
        $this->assertFalse($post->isPublished());
        $this->assertFalse($post->isUnpublished());
    }

    public function testCannersDefaultValues()
    {
        $post = new Post();

        $this->assertTrue($post->canPublish());
        $this->assertFalse($post->canUnpublish());
    }

    public function testPublish()
    {
        $post = new Post();

        $this->assertTrue($post->isDraft());
        $this->assertFalse($post->isPublished());
        $this->assertFalse($post->isUnpublished());
        $this->assertTrue($post->canPublish());

        try {
            $post->publish();
        } catch (Exception $e) {
            $this->fail('Unexpected exception caught: ' . $e->getMessage());
        }

        $this->assertFalse($post->canPublish());
        $this->assertTrue($post->canUnpublish());
        $this->assertFalse($post->isDraft());
        $this->assertTrue($post->isPublished());
        $this->assertFalse($post->isUnpublished());
    }
}

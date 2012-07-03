<?php

use My\Post;
use My\PostWithCustomColumn;

/**
 * @author William Durand <william.durand1@gmail.com>
 */
class StateMachineBehaviorWithNamespacesTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (!class_exists('My\Post')) {
            $schema = <<<EOF
<database name="state_machine_behavior" defaultIdMethod="native" namespace="My">
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
    <table name="post_with_custom_column">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="draft, published, not_yet_published" />

            <parameter name="initial_state" value="draft" />

            <parameter name="transition" value="draft to published with publish" />
            <parameter name="transition" value="published to not_yet_published with unpublish" />
            <parameter name="transition" value="not_yet_published to published with publish" />

            <parameter name="state_column" value="my_state" />
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
        $this->assertTrue(method_exists('My\Post', 'isDraft'));
        $this->assertTrue(method_exists('My\Post', 'isUnpublished'));
        $this->assertTrue(method_exists('My\Post', 'isPublished'));

        $this->assertTrue(method_exists('My\Post', 'canPublish'));
        $this->assertTrue(method_exists('My\Post', 'canUnpublish'));

        $this->assertTrue(method_exists('My\Post', 'publish'));
        $this->assertTrue(method_exists('My\Post', 'unpublish'));

        $this->assertTrue(method_exists('My\Post', 'prePublish'));
        $this->assertTrue(method_exists('My\Post', 'onPublish'));
        $this->assertTrue(method_exists('My\Post', 'postPublish'));

        $this->assertTrue(method_exists('My\Post', 'preUnpublish'));
        $this->assertTrue(method_exists('My\Post', 'onUnpublish'));
        $this->assertTrue(method_exists('My\Post', 'postUnpublish'));

        $this->assertTrue(method_exists('My\Post', 'getAvailableStates'));
        $this->assertTrue(method_exists('My\Post', 'getState'));
        $this->assertTrue(method_exists('My\Post', 'getHumanizedState'));
        $this->assertTrue(method_exists('My\Post', 'getHumanizedStates'));

        $this->assertTrue(defined('My\Post::STATE_DRAFT'));
        $this->assertTrue(defined('My\Post::STATE_PUBLISHED'));
        $this->assertTrue(defined('My\Post::STATE_UNPUBLISHED'));
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
            Post::STATE_UNPUBLISHED,
            Post::STATE_PUBLISHED,
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

    public function testSymbolMethodShouldThrowAnExceptionOnInvalidCall()
    {
        $post = new Post();

        $this->assertFalse($post->canUnpublish());

        try {
            $post->unpublish();
            $this->fail('Expected exception not thrown') ;
        } catch (Exception $e) {
            $this->assertTrue(true);
            $this->assertInstanceOf('LogicException', $e);
        }

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

        try {
            $post->publish();
            $this->fail('Expected exception not thrown') ;
        } catch (Exception $e) {
            $this->assertTrue(true);
            $this->assertInstanceOf('LogicException', $e);
        }
    }

    public function testGenerateGetStateIfCustomStateColumn()
    {
        $this->assertTrue(method_exists('My\PostWithCustomColumn', 'getState'));
        $this->assertTrue(method_exists('My\PostWithCustomColumn', 'getMyState'));
        $this->assertTrue(method_exists('My\PostWithCustomColumn', 'isNotYetPublished'));

        $this->assertTrue(defined('My\PostWithCustomColumn::STATE_NOT_YET_PUBLISHED'));
    }

    public function testIssersDefaultValuesWithCustomStateColumn()
    {
        $post = new PostWithCustomColumn();

        $this->assertTrue($post->isDraft());
        $this->assertFalse($post->isPublished());
        $this->assertFalse($post->isNotYetPublished());
    }

    public function testCannersDefaultValuesWithCustomStateColumn()
    {
        $post = new PostWithCustomColumn();

        $this->assertTrue($post->canPublish());
        $this->assertFalse($post->canUnpublish());
    }

    public function testGetHumanizedState()
    {
        $post = new PostWithCustomColumn();
        $this->assertEquals('Draft', $post->getHumanizedState());

        $refl = new ReflectionClass($post);
        $prop = $refl->getProperty('my_state');
        $prop->setAccessible(true);
        $prop->setValue($post, PostWithCustomColumn::STATE_NOT_YET_PUBLISHED);

        $this->assertEquals('Not Yet Published', $post->getHumanizedState());
    }

    public function testGetAvailableStatesStatic()
    {
        $post = new Post();

        $this->assertEquals($post->getAvailableStates(), Post::getAvailableStates());
    }

    public function testGetHumanizedStates()
    {
        $expected = array(
            Post::STATE_DRAFT       => 'Draft',
            Post::STATE_UNPUBLISHED => 'Unpublished',
            Post::STATE_PUBLISHED   => 'Published',
        );

        $this->assertTrue(is_array(Post::getHumanizedStates()));
        $this->assertEquals($expected, Post::getHumanizedStates());
    }
}

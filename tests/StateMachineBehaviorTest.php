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
    <table name="post_with_custom_column">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="draft, published, not_yEt_published, flagged" />

            <parameter name="initial_state" value="draft" />

            <parameter name="transition" value="draft to published with publish" />
            <parameter name="transition" value="published to not_yet_published with unpublish" />
            <parameter name="transition" value="not_yEt_published to published with publish" />
            <parameter name="transition" value="not_yEt_published to flagged with flag_for_publish" />
            <parameter name="transition" value="flagged to published with publish" />

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
        $this->assertTrue(method_exists('Post', 'getHumanizedState'));
        $this->assertTrue(method_exists('Post', 'getHumanizedStates'));

        $this->assertTrue(defined('Post::STATE_DRAFT'));
        $this->assertTrue(defined('Post::STATE_PUBLISHED'));
        $this->assertTrue(defined('Post::STATE_UNPUBLISHED'));

        $this->assertTrue(defined('Post::STATE_NORMALIZED_DRAFT'));
        $this->assertEquals('draft', Post::STATE_NORMALIZED_DRAFT);
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

    public function testGetNormalizedState()
    {
        $post = new Post();
        $this->assertEquals(Post::STATE_NORMALIZED_DRAFT, $post->getNormalizedState());
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
        $this->assertTrue(method_exists('PostWithCustomColumn', 'getState'));
        $this->assertTrue(method_exists('PostWithCustomColumn', 'getMyState'));
        $this->assertTrue(method_exists('PostWithCustomColumn', 'isNotYetPublished'));
        $this->assertTrue(method_exists('PostWithCustomColumn', 'flagForPublish'));

        $this->assertTrue(defined('PostWithCustomColumn::STATE_NOT_YET_PUBLISHED'));
        $this->assertTrue(defined('PostWithCustomColumn::STATE_NORMALIZED_NOT_YET_PUBLISHED'));
        $this->assertEquals('not_yet_published', PostWithCustomColumn::STATE_NORMALIZED_NOT_YET_PUBLISHED);
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

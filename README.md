StateMachineBehavior
====================

[![Build Status](https://secure.travis-ci.org/willdurand/StateMachineBehavior.png?branch=master)](http://travis-ci.org/willdurand/StateMachineBehavior)

This behavior adds a finite state machine to your model.


### Configuration ###

``` xml
<behavior name="state_machine">
    <parameter name="states" value="draft, rejected, unpublished, published" />

    <parameter name="initial_state" value="draft" />

    <parameter name="transition" value="draft to published with publish" />
    <parameter name="transition" value="draft to rejected with reject" />
    <parameter name="transition" value="published to unpublished with unpublish" />
    <parameter name="transition" value="unpublished to published with publish" />

    <!-- Optional parameters -->
    <parameter name="state_column" name="state" />
</behavior>
```

The **state_machine** behavior requires three parameters to work:

* `states`: a finite set of states as comma separated values;
* `initial_state`: the initial state, part of set of states;
* `transition`: a set of transitions. As you can see, you can add as many `transition` parameters as you want.

Each transition has to follow this pattern:

    STATE_1 to STATE_2 with SYMBOL

A `symbol`, which is part of the Finite State Machine's terminology, can be considered as an event triggered
on your model object.


### ActiveRecord API ###

The behavior will generate the following constants which represent the available states of your object:

* `ObjectModel::STATE_DRAFT`
* `ObjectModel::STATE_REJECTED`
* `ObjectModel::STATE_UNPUBLISHED`
* `ObjectModel::STATE_PUBLISHED`

You can get the current state of your object:

    getState()

Or get an array with all available states:

    getAvailableStates()

The behavior will also generate a set of issers:

    isDraft()

    isRejected()

    isPublished()

    isUnpublished()

But the most interesting part is the implemenation of the FSM itself.
First you have methods to determine whether you can perform, or not a transition based
on the current model's state:

    canPublish()

    canReject()

    canUnpublish()

It will also generate a set of methods for each `symbol`:

    publish(PropelPDO $con = null)

    unpublish(PropelPDO $con = null)

    reject(PropelPDO $con = null)


To handle custom logic, new hooks are created.
The methods below should return a boolean value, and can act as **guards** (which is not part
of the FSM's terminology).

    prePublish(PropelPDO $con = null)

    preUnpublish(PropelPDO $con = null)

    preReject(PropelPDO $con = null)

The methods below should contain your own logic depending on each state, and your business.

    onPublish(PropelPDO $con = null)

    onUnpublish(PropelPDO $con = null)

    onReject(PropelPDO $con = null)

The methods below allow to execute code once the transition is executed.

    postPublish(PropelPDO $con = null)

    postUnpublish(PropelPDO $con = null)

    postReject(PropelPDO $con = null)


### ActiveQuery API ###

To be defined.


### Usage ###

Let's say we have a `Post` model class which represents an entry in a blog engine.
When we create a new post, its initial state is `draft` because we don't want to publish
it immediately.
As a `draft`, you can decide to publish your new post. Its state is now `published`.
Once `published`, you may want to unpublish it for some reasons. Then, its state is `unpublished`.
The last possibily is to republish an `unpublished` post. The new state is `published`.

We have three different states (`draft`, `published`, `unpublished`), and three transitions:

* `draft` to `published`
* `published` to `unpublished`
* `unpublished` to `published`

We can define the following configuration:

``` xml
<table name="post">
    <!-- some columns -->

    <behavior name="state_machine">
        <parameter name="states" value="draft, unpublished, published" />

        <parameter name="initial_state" value="draft" />

        <parameter name="transition" value="draft to published with publish" />
        <parameter name="transition" value="published to unpublished with unpublish" />
        <parameter name="transition" value="unpublished to published with publish" />
    </behavior>
</table>
```

Here is a workflow:

``` php
<?php

$post = new Post();

$post->getState();              // Post::STATE_DRAFT
$post->getAvailableStates();    // Post::STATE_DRAFT, Post::STATE_UNPUBLISHED, Post::STATE_PUBLISHED

$post->isDraft();               // true
$post->isPublished();           // false
$post->isUnpublished();         // false

$post->canPublish();            // true
$post->canUnpublish();          // false

$post->unpublish();             // throw a LogicException, no transition found from draft to unpublished

// Let's publish this post
// This is the first transition in the scenario above
$post->publish()->save();

$post->isDraft();               // false
$post->isPublished();           // true
$post->isUnpublished();         // false

$post->canPublish();            // false
$post->canUnpublish();          // true

$post->publish();               // throw a LogicException, the post is already published

// Let's unpublish this post
// This is the second transition in the scenario above
$post->unpublish()->save();

$post->isDraft();               // false
$post->isPublished();           // false
$post->isUnpublished();         // true

$post->canPublish();            // true
$post->canUnpublish();          // false

$post->unpublish();             // throw a LogicException, the post is already unpublished

// Let's (re)publish this post
// This is the last transition in the scenario above
$post->publish()->save();

$post->isDraft();               // false
$post->isPublished();           // true
$post->isUnpublished();         // false

$post->canPublish();            // false
$post->canUnpublish();          // true
```

Now imagine we have authors linked to each post, and once a post is published,
we notify the post's author by email. Thanks to new hooks, it's
really easy to extend things:

``` php
<?php

class Post extends BasePost
{
    // Assuming we have a mail manager which is able to send emails,
    // and that we injected it before.
    private $mailManager;

    public function onPublish(PropelPDO $con = null)
    {
        $this->mailManager->postPublished(
            $this->getAuthor(),
            $this->getTitle()
        );
    }
}
```

Use case in a controller:

``` php
<?php

class PostController extends Controller
{
    public function newAction()
    {
        // handle a form, etc to create a new Post
    }

    public function publishAction(Post $post)
    {
        try {
            $post->publish()->save();
        } catch (\LogicException $e) {
            // handle the exception as you wish
        }
    }

    public function unpublishAction(Post $post)
    {
        try {
            $post->unpublish()->save();
        } catch (\LogicException $e) {
            // handle the exception as you wish
        }
    }
}
```


### Known Limitations ###

* You cannot use the `deleted` state;
* You cannot use the `save`, or `delete` symbols.

At the moment, there is no built-in solution to handle these cases.


### Combining Archivable Behavior

The [Archivable](http://www.propelorm.org/behaviors/archivable.html) behavior is
useful to copy a model object to an archival table. In other words, it acts as
a soft delete behavior but with better performance.

In your workflow, you may want to destroy your object for some reason. I say
"destroy" because you can't use the `deleted` status, nor the `delete` symbol,
but it doesn't matter. Destroying an object is fine, but instead of hard
deleting it, you may want to soft delete it. That means you will rely on the
archivable behavior.

Just add it to your XML schema, rebuild both SQL, and model classes, and you're
done. At first glance, when you `destroy` your object, you will expect it
to be hidden, but it's not the case. It just has the `destroyed` state.

Thanks to hooks, you just have to call the `delete()` method to your object to
fit your expectations. This method is tweaked by the archivable behavior to soft
delete your object.

``` php
<?php

class Post extends BasePost
{
    /**
     * {@inheritdoc}
     */
    public function postDestroy(PropelPDO $con = null)
    {
        $this->delete($con);
    }
}
```

But, why should I put my logic in the post hook? The main reason is that a
symbol method changes the state of the object, and then saves it. What you want
is to archive the object at its last state.
By putting the logic in the post hook, your object will have the `destroyed`
state before to be archived, and you will archive the last state of your object.

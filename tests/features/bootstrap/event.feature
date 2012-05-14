Feature: State Machine
    In order to manage a workflow
    As a developer
    I need to use the awesome StateMachineBehavior

    Scenario: Publish/Unpublish an Event
        Given the following XML schema:
            """
<database name="state_machine_behavior" defaultIdMethod="native">
    <table name="event">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="draft, unpublished, published, rejected, destroyed" />

            <parameter name="initial_state" value="draft" />

            <parameter name="transition" value="draft to published with publish" />
            <parameter name="transition" value="draft to rejected with reject" />
            <parameter name="transition" value="draft to destroyed with destroy" />
            <parameter name="transition" value="published to unpublished with unpublish" />
            <parameter name="transition" value="unpublished to published with publish" />
            <parameter name="transition" value="rejected to published with publish" />
            <parameter name="transition" value="rejected to destroyed with destroy" />
        </behavior>
    </table>
</database>
            """
        And I want to manage an "Event"
        And Its default state is "draft"

        When I "publish" it
        Then I should get a "published" state
        And I should be able to "unpublish" it
        But I should not be able to "publish" it again
        And I should not be able to "reject" it
        And I should not be able to "destroy" it

        When I "unpublish" it
        Then I should get a "unpublished" state
        And I should be able to "publish" it
        But I should not be able to "unpublish" it again
        And I should not be able to "reject" it
        And I should not be able to "destroy" it

    Scenario: Reject/Publish/Unpublish an Event
        Given I want to manage an "Event"
        And Its default state is "draft"

        When I "reject" it
        Then I should get a "rejected" state
        And I should be able to "destroy" it
        And I should be able to "publish" it
        But I should not be able to "reject" it again
        And I should not be able to "unpublish" it

        When I "publish" it
        Then I should get a "published" state
        And I should be able to "unpublish" it
        But I should not be able to "publish" it again
        And I should not be able to "reject" it
        And I should not be able to "destroy" it

        When I "unpublish" it
        Then I should get a "unpublished" state
        And I should be able to "publish" it
        But I should not be able to "unpublish" it again
        And I should not be able to "reject" it
        And I should not be able to "destroy" it

    Scenario: Reject/destroy an Event
        Given I want to manage an "Event"
        And Its default state is "draft"

        When I "reject" it
        Then I should get a "rejected" state
        And I should be able to "destroy" it
        And I should be able to "publish" it
        But I should not be able to "reject" it again
        And I should not be able to "unpublish" it

        When I "destroy" it
        Then I should get a "destroyed" state
        And I should not be able to "destroy" it again
        And I should not be able to "publish" it
        And I should not be able to "unpublish" it
        And I should not be able to "reject" it

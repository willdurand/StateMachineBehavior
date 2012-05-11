Feature: State Machine
    In order to manage a workflow
    As a developer
    I need to use the awesome StateMachineBehavior

    Scenario:
        Given the following XML schema:
            """
<database name="state_machine_behavior" defaultIdMethod="native">
    <table name="traffic_light">
        <column name="id" required="true" primaryKey="true" autoIncrement="true" type="INTEGER" />

        <behavior name="state_machine">
            <parameter name="states" value="red, orange, green" />

            <parameter name="initial_state" value="red" />

            <parameter name="transition" value="red to orange with prepare" />
            <parameter name="transition" value="orange to green with start" />
            <parameter name="transition" value="green to orange with prepare" />
            <parameter name="transition" value="orange to red with stop" />
        </behavior>
    </table>
</database>
            """
        And I want to manage a "TrafficLight"
        When I "prepare" it
        Then I should get an "orange" state
        And I should be able to "start" it

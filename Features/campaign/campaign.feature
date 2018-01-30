@campaign
Feature: campaign
  I need to be able to push data to screens

  Background:
    When the following channels exist:
      | id | title                    | created_at | modified_at |
      | 0  | Channel 1                | 0          | 0           |
      | 1  | Channel 2                | 1          | 1           |
      | 2  | Hostile Takeover Channel | 2          | 2           |
    And the following screens exist:
      | title    | channel | created_at | modified_at |
      | Screen 1 | 0       | 0          | 0           |
      | Screen 2 | 1       | 1          | 1           |
    And the following users exist:
      | username | password | roles     | groups |
      | user     | user     | ROLE_USER |        |
    And I sign in with username "user" and password "user"

  @createSchema
  Scenario: Create schema

  Scenario: Assert that channel 1 is pushed to screen 2
    When I call pushToScreens
    And I get all the utility service curl calls with prefix middleware
    And I print all the utility service curl calls
    Then curl calls should equal:
      | url                                            | method | data                                                                                                                                                    | prefix     |
      | https://middleware.os2display.vm/api/channel/1 | POST   | {"title":"Channel 1","data":{"id":1,"slides":[],"schedule_repeat_days":[]},"regions":[{"screen":2,"region":0}],"screens":[2],"schedule_repeat_days":[]} | middleware |
      | https://middleware.os2display.vm/api/channel/2 | DELETE | []                                                                                                                                                      | middleware |
      | https://middleware.os2display.vm/api/channel/3 | DELETE | []                                                                                                                                                      | middleware |
    And channel 1 should be pushed to screen 2
    And channel 1 should not be pushed to screen 3

  Scenario: Assert that campaign will take over other channels
    When the following 'Itk\CampaignBundle\Entity\Campaign' entities exist:
      | id | title              | user | description | schedule_from | schedule_to | screens | channels |
      | 1  | The first campaign | 0    | -           | 2001-01-01    | 2020-01-01  | 0: 1    | 0: 2     |
    And I get all the utility service curl calls with prefix middleware
    Then channel 2 should be pushed to screen 1

  @dropSchema
  Scenario: Drop schema

@campaign
Feature: campaign
  Background:
    Given the following channels exist:
      | title                     | created_at | modified_at |
      | Channel 1                 | 0          | 0           |
      | Channel 2                 | 1          | 1           |
      | Hostile Takeover Channel  | 2          | 2           |
    And the following slides exist:
      | title     | channel   | created_at | modified_at |
      | Slide 1   | 0         | 0          | 0           |
      | Slide 2   | 1         | 1          | 1           |
      | HTslide   | 2         | 2          | 2           |
    And the following screens exist:
      | title     | channel   | created_at | modified_at |
      | Screen 1  | 0         | 0          | 0           |
      | Screen 2  | 1         | 1          | 1           |
    And the following users exist:
      | username | password | roles     | groups |
      | user     | user     | ROLE_USER |        |
    And I sign in with username "user" and password "user"

  @createSchema
  Scenario: Create schema

  Scenario: Assert that channel 1 is pushed to screen 2
    When I call pushToScreens
    And I get all the utility service curl calls with prefix middleware
    Then curl calls should equal:
      | url | method | data | prefix |
      | https://middleware.os2display.vm/api/channel/1 | POST   | {"title":"Channel 1","data":{"id":1,"slides":[],"schedule_repeat_days":[]},"regions":[{"screen":2,"region":0}],"screens":[2],"schedule_repeat_days":[]} | middleware |
      | https://middleware.os2display.vm/api/channel/2 | DELETE | [] | middleware |
      | https://middleware.os2display.vm/api/channel/3 | DELETE | [] | middleware |
    And channel 1 should be pushed to screen 2
    And channel 1 should not be pushed to screen 3

  @dropSchema
  Scenario: Drop schema

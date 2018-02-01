@campaign
Feature: campaign
  I need to be able to push data to screens

  Background:
    When the following channels exist:
      | title                    | created_at | modified_at |
      | Channel 1                | 0          | 0           |
      | Channel 2                | 1          | 1           |
      | Hostile Takeover Channel | 2          | 2           |
    And the following screens exist:
      | title    | channel | created_at | modified_at |
      | Screen 1 | 1       | 0          | 0           |
    And the following users exist:
      | username | password | roles                                      | groups |
      | user     | user     | ROLE_USER, ROLE_CAMPAIGN_ADMIN, ROLE_ADMIN |        |
    And I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"

  @createSchema
  Scenario: Create schema

  Scenario: Assert that channel 1 is pushed to screen 1 and not pushed the second time since there are no changes.
    When I call pushToScreens
    And I get all the utility service curl calls with prefix middleware
    Then curl calls should equal:
      | url                                            | method | data                                                                                                                                                    | prefix     |
      | https://middleware.os2display.vm/api/channel/1 | POST   | {"title":"Channel 1","data":{"id":1,"slides":[],"schedule_repeat_days":[]},"regions":[{"screen":1,"region":1}],"screens":[1],"schedule_repeat_days":[]} | middleware |
    And channel 1 should be pushed to screen 1
    And channel 2 should not be pushed to screen 1
    And I clear utility service
    And I call pushToScreens
    And I get all the utility service curl calls with prefix middleware
    Then curl calls should equal:
      | url                                            | method | data                                                                                                                                                    | prefix     |

  Scenario: Assert that campaign will take over other channels
    When I clear all channels
    And I clear utility service
    And I send a "POST" request to "/api/campaign" with body:
    """
    {
      "title": "The first campaign",
      "schedule_from": "2001-01-01",
      "schedule_to": "2041-01-31",
      "groups": [],
      "channels": [3],
      "screen_groups": [],
      "screens": [1]
    }
    """
    And the response status code should be 201

    And I send a "GET" request to "/api/channel"
    And print last JSON response

    And I send a "GET" request to "/api/screen"
    And print last JSON response

    And I print all channel screen regions

    And I call pushToScreens
    And I print all the utility service curl calls
    And I get all the utility service curl calls with prefix middleware
    Then channel 1 should not be pushed to screen 1
    And channel 2 should not be pushed to screen 1
    And channel 3 should be pushed to screen 1

  @dropSchema
  Scenario: Drop schema

@api @campaign
Feature: campaign
  In order to …
  As a client …
  I need to be able to …

  Background:
    Given the following groups exist:
      | title   |
      | Group 1 |
      | Group 2 |
      | Group 3 |
      | Group 4 |

    And the following users exist:
      | username | password | roles                          | groups                                                      |
      | user     | user     | ROLE_USER, ROLE_CAMPAIGN_ADMIN | 1: GROUP_ROLE_USER, 2: GROUP_ROLE_USER                      |
      | user1    | user1    | ROLE_USER                      | 1: GROUP_ROLE_ADMIN, 2: GROUP_ROLE_USER, 3: GROUP_ROLE_USER |
      | user2    | user2    | ROLE_USER                      | 1: GROUP_ROLE_USER, 2: GROUP_ROLE_USER, 4: GROUP_ROLE_USER  |

    And I sign in with username "user" and password "user"

  @createSchema
  Scenario: Get campaigns
    When I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
      """
      []
      """

  Scenario: Create campaign
    When I add "content-type" header equal to "application/json"
    And I send a "POST" request to "/api/campaign" with body:
    """
    {
      "title": "The first campaign",
      "schedule_from": "2001-01-01",
      "schedule_to": "2001-01-31",
      "groups": [1]
    }
    """
    Then the response status code should be 201

  Scenario: Get campaigns
    When I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 1 element
    And the JSON node "[0].id" should be equal to 1
    And the JSON node "[0].title" should be equal to "The first campaign"
    And the JSON node "[0].schedule_from" should be equal to "2001-01-01T00:00:00+0100"
    And the JSON node "[0].schedule_to" should be equal to "2001-01-31T00:00:00+0100"

  Scenario: Update campaign
    When I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "title": "The first campaign (updated)"
    }
    """
    Then the response status code should be 200

  Scenario: Set groups on campaign
    When I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "groups": [1,3]
    }
    """
    Then the response status code should be 200

  Scenario: Get campaigns
    When I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 1 element
    And the JSON node "[0].id" should be equal to 1
    And the JSON node "[0].title" should be equal to "The first campaign (updated)"

  Scenario: Remove campaign
    When I send a "DELETE" request to "/api/campaign/1"
    Then the response status code should be 204

  Scenario: Get campaigns
    When I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 0 elements

  @dropSchema
  Scenario: Drop schema

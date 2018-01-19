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
      | username | password | roles                          | groups                                 |
      | user     | user     | ROLE_USER, ROLE_CAMPAIGN_ADMIN | 1: GROUP_ROLE_USER, 2: GROUP_ROLE_USER |
      | user1    | user1    | ROLE_USER                      | 3: GROUP_ROLE_USER                     |
      | user2    | user2    | ROLE_USER                      | 1: GROUP_ROLE_USER, 4: GROUP_ROLE_USER |

    And the following 'Os2Display\CoreBundle\Entity\Channel' entities identified by 'id' exist:
      | id | title             |  createdAt | modifiedAt |
      |  1 | The first channel | 2001-01-01 | 2001-01-01 |
      |  3 | Channel 3         | 2001-01-01 | 2001-01-01 |

    And the following 'Os2Display\CoreBundle\Entity\Screen' entities exist:
      | id | title            | description | token      | activationCode |  createdAt | modifiedAt |
      |  1 | The first screen | -           | screen-001 | activate-001   | 2001-01-01 | 2001-01-01 |
      |  2 | Another screen   | -           | screen-002 | activate-002   | 2001-01-01 | 2001-01-01 |

  @createSchema
  Scenario: Get campaigns
    When I sign in with username "user" and password "user"
    And I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
      """
      []
      """

  Scenario: Create campaign
    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
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
    When I sign in with username "user" and password "user"
    And I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 1 element
    And the JSON node "[0].id" should be equal to 1
    And the JSON node "[0].title" should be equal to "The first campaign"
    And the JSON node "[0].schedule_from" should be equal to "2001-01-01T00:00:00+0100"
    And the JSON node "[0].schedule_to" should be equal to "2001-01-31T00:00:00+0100"

  Scenario: Update campaign
    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "title": "The first campaign (updated)"
    }
    """
    Then the response status code should be 200

  Scenario: Set groups on campaign
    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "groups": [1,3]
    }
    """
    Then the response status code should be 200
    # "user" is only a member of one group (id: 1)
    And the JSON node "groups" should have 1 element
    And the JSON node "groups[0].id" should be equal to 1

    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "groups": [1,2]
    }
    """
    Then the response status code should be 200

    When I sign in with username "user" and password "user"
    And I send a "GET" request to "/api/campaign/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "groups" should have 2 elements

    When I sign in with username "user1" and password "user1"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "title": "Updated by user1"
    }
    """
    # user1 is not a member of groups 1 or 2
    Then the response status code should be 403

    When I sign in with username "user2" and password "user2"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "groups": [1,4]
    }
    """
    Then the response status code should be 200

    When I sign in with username "user2" and password "user2"
    And I send a "GET" request to "/api/campaign/1"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "groups" should have 3 elements

  Scenario: Set channels on campaign
    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "channels": [1]
    }
    """
    Then the response status code should be 200
    And the JSON node "channels" should have 1 element
    And the JSON node "channels[0].title" should be equal to "The first channel"

    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "channels": [1,3]
    }
    """
    Then the response status code should be 200
    And the JSON node "channels" should have 2 elements

  Scenario: Set screens on campaign
    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "screens": [2]
    }
    """
    Then the response status code should be 200
    And the JSON node "screens" should have 1 element
    And the JSON node "screens[0].title" should be equal to "Another screen"

    When I sign in with username "user" and password "user"
    And I add "content-type" header equal to "application/json"
    And I send a "PUT" request to "/api/campaign/1" with body:
    """
    {
      "screens": [2,1]
    }
    """
    Then the response status code should be 200
    And the JSON node "screens" should have 2 elements

  Scenario: Get campaigns
    When I sign in with username "user" and password "user"
    And I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 1 element
    And the JSON node "[0].id" should be equal to 1
    And the JSON node "[0].title" should be equal to "The first campaign (updated)"

  Scenario: Remove campaign
    When I sign in with username "user" and password "user"
    And I send a "DELETE" request to "/api/campaign/1"
    Then the response status code should be 204

  Scenario: Get campaigns
    When I sign in with username "user" and password "user"
    And I send a "GET" request to "/api/campaign"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON node "" should have 0 elements

  @dropSchema
  Scenario: Drop schema

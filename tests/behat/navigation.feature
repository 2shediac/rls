@local @local_rlsiteadmin @local_rlsiteadmin_navigation @javascript
Feature: Links added to navigation block to site admin features.

    Scenario: Navigation menu links show up for administrators.
        When I log in as "admin"
        And I am on homepage
        Then "My RL Admin Tools" "text" should exist in the "Navigation" "block"
        And "Site Dashboard" "link" should exist in the "Navigation" "block"
        And "Add-on Manager" "link" should exist in the "Navigation" "block"
        And the "href" attribute of "//div[contains(@class, 'block_navigation')]//span[text()='Site Dashboard']/parent::a" "xpath_element" should contain "local/rlsiteadmin/dashboard"
        And the "href" attribute of "//div[contains(@class, 'block_navigation')]//span[text()='Add-on Manager']/parent::a" "xpath_element" should contain "local/rlsiteadmin/mass"
        And the "href" attribute of "//div[contains(@class, 'block_navigation')]//span[text()='BackTrack Archives']/parent::a" "xpath_element" should contain "local/rlsiteadmin/archive"

    Scenario: Navigation menu links do not show up for non-administrators.
        Given the following "users" exist:
          | username | firstname | lastname | email |
          | student1 | John | Doe | student1@example.com |
        When I log in as "student1"
        And I am on homepage
        Then "My RL Admin Tools" "text" should not exist in the "Navigation" "block"
        And "Site Dashboard" "link" should not exist in the "Navigation" "block"
        And "Add-on Manager" "link" should not exist in the "Navigation" "block"


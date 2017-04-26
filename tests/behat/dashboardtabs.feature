@local @local_rlsiteadmin @javascript
Feature: Plugin extends navigation menu

    Scenario: Non-admin user cannot navigate to the dashboard page
        Given the following "users" exist:
           | username | firstname | lastname | email |
           | student | Student | User | student@behat.com |
        And I log in as "student"
        And I am on homepage
        Then I should not see "My RL Admin Tools" in the "Navigation" "block"
        And I should not see "Site Dashboard" in the "Navigation" "block"
        And I should not see "Add-on Manager" in the "Navigation" "block"

    Scenario: Non-admin user cannot directly access the dashboard page
        Given the following "users" exist:
           | username | firstname | lastname | email |
           | student | Student | User | student@behat.com |
        And I log in as "student"
        And I go to dashboard
        And I wait until the page is ready
        Then I should see "You must be a site administrator to access this functionality."

    Scenario: Navigation block shows plugin links
        Given I log in as "admin"
        And I am on homepage
        Then I should see "My RL Admin Tools" in the "Navigation" "block"
        And I should see "Site Dashboard" in the "Navigation" "block"
        And I should see "Add-on Manager" in the "Navigation" "block"
        And "Site Dashboard" "link" should appear after "My RL Admin Tools" "text"
        And "Add-on Manager" "link" should appear after "My RL Admin Tools" "text"
        And "Add-on Manager" "link" should appear after "Site Dashboard" "link"

    Scenario: Dashboard link goes to dashboard page
        Given I log in as "admin"
        And I am on homepage
        Then I should see "My RL Admin Tools" in the "Navigation" "block"
        And I navigate to "Site Dashboard" node in "My RL Admin Tools"
        Then I should see "Site Dashboard" in the "//h2" "xpath_element"

    Scenario: Tabs are shown on dashboard page
        Given I log in as "admin"
        And I am on homepage
        And I navigate to "Site Dashboard" node in "My RL Admin Tools"
        Then I should see "Site Dashboard" in the "//h2" "xpath_element"
        And "Information & News" "link" should appear after "Site Dashboard" "text"
        And "Support" "link" should appear after "Information & News" "link"
        And "Reports" "link" should appear after "Support" "link"
        And "a[href='#info']" "css_element" should exist in the ".nav-tabs .active" "css_element"
        And ".block.rl-dashboard-widget" "css_element" should exist in the ".tab-pane.active" "css_element"

    Scenario: Help information is visible for each dashboard tab
        Given I log in as "admin"
        And I am on homepage
        And I navigate to "Site Dashboard" node in "My RL Admin Tools"
        And "a[href='#info']" "css_element" should exist in the ".nav-tabs .active" "css_element"
        And "div.rl-dashboard-wells .rl-dashboard-info-well" "css_element" should be visible
        And "div.rl-dashboard-wells .rl-dashboard-support-well" "css_element" should not be visible
        And "div.rl-dashboard-wells .rl-dashboard-reports-well" "css_element" should not be visible
        Then I click on "a[href='#reports']" "css_element"
        And I wait "5" seconds
        And "div.rl-dashboard-wells .rl-dashboard-info-well" "css_element" should not be visible
        And "div.rl-dashboard-wells .rl-dashboard-support-well" "css_element" should not be visible
        And "div.rl-dashboard-wells .rl-dashboard-reports-well" "css_element" should be visible
        Then I click on "a[href='#support']" "css_element"
        And "div.rl-dashboard-wells .rl-dashboard-info-well" "css_element" should not be visible
        And "div.rl-dashboard-wells .rl-dashboard-support-well" "css_element" should be visible
        And "div.rl-dashboard-wells .rl-dashboard-reports-well" "css_element" should not be visible
        Then I click on "a[href='#info']" "css_element"
        And "div.rl-dashboard-wells .rl-dashboard-info-well" "css_element" should be visible
        And "div.rl-dashboard-wells .rl-dashboard-support-well" "css_element" should not be visible
        And "div.rl-dashboard-wells .rl-dashboard-reports-well" "css_element" should not be visible

    Scenario: Site details contains data
        Given I log in as "admin"
        And I am on homepage
        And the following "courses" exist:
          | fullname | shortname | category |
          | Course 1 | C1 | 0 |
        And I click on "Site Dashboard" "link" in the "Navigation" "block"
        Then I should see "Site Dashboard" in the "//h2" "xpath_element"
        And I click on "Information & News" "link"
        Then I should see "Site Details" in the ".moodlestats-inner .navbar-default.widget-header" "css_element"
        Then I should see "1" in the "//tr[@class='activeusers']/td" "xpath_element"
        Then I should see "1" in the "//tr[@class='totalusers']/td" "xpath_element"
        Then I should see "1" in the "//tr[@class='totalcourses']/td" "xpath_element"

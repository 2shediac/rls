@local @local_rlsiteadmin @local_rlsiteadmin_mass @javascript
Feature: MASS - Moodle Addon Self Service.

    Scenario: Updating of upgrade settings for manual and automatic.
        When I log in as "admin"
        And I load mass data
        And I am on homepage
        And I go to mass
        And "Appointments" "list_item" should exist in the ".name-course_appointments" "css_element"
        And "Checklist" "list_item" should exist in the ".name-checklist" "css_element"
        And "Google OpenId Authentication" "list_item" should exist in the ".name-gauth" "css_element"
        Then the "upgrademethod_block_annotate" field should contain "manual"
        Then the "upgrademethod_local_aspiredu" field should contain "manual"
        And I click on "#local_aspiredu_auto" "css_element"
        Then the "upgrademethod_local_aspiredu" field should contain "auto"
        And The upgrade setting for "block_annotate" should be "manual"
        And The upgrade setting for "local_aspiredu" should be "auto"
        And I click on "#local_aspiredu_manual" "css_element"
        And The upgrade setting for "local_aspiredu" should be "manual"
        And I click on "#local_aspiredu_auto" "css_element"
        And The upgrade plugins task is executed
        And The update plugins task has created mass dispatcher file

    Scenario: Verify override upgrade settings disable radio buttons.
        When I log in as "admin"
        And I load mass data
        And I am on homepage
        And I go to mass
        And "Appointments" "list_item" should exist in the ".name-course_appointments" "css_element"
        And "Google OpenId Authentication" "list_item" should exist in the ".name-gauth" "css_element"
        And "Blackboard Collaborate" "list_item" should exist in the ".name-elluminate" "css_element"
        And "Checklist" "list_item" should exist in the ".name-checklist" "css_element"
        And "#auth_gauth_manual" "css_element" should not exist
        And "#auth_gauth_auto" "css_element" should not exist
        And "#block_elluminate_manual" "css_element" should not exist
        And "#block_elluminate_auto" "css_element" should not exist
        And "#local_aspiredu_auto" "css_element" should exist
        And "#local_aspiredu_manual" "css_element" should exist
        And "#block_course_appointments_manual" "css_element" should not exist
        And "#block_course_appointments_auto" "css_element" should not exist
        And "#block_checklist_manual" "css_element" should not exist
        And "#block_checklist_auto" "css_element" should not exist

    Scenario: Set all plugins to automatic or manual.
        When I log in as "admin"
        And I load mass data
        And I am on homepage
        And I go to mass
        And I should see "Appointments" in the ".name-course_appointments .media-heading" "css_element"
        Then the "upgrademethod_block_annotate" field should contain "manual"
        Then the "upgrademethod_local_aspiredu" field should contain "manual"
        And I click on "#upgradesettings_all_auto" "css_element"
        Then the "upgrademethod_block_annotate" field should contain "auto"
        Then the "upgrademethod_local_aspiredu" field should contain "auto"
        And The upgrade setting for "block_annotate" should be "auto"
        And The upgrade setting for "local_aspiredu" should be "auto"
        And I click on "#upgradesettings_all_manual" "css_element"
        Then the "upgrademethod_block_annotate" field should contain "manual"
        Then the "upgrademethod_local_aspiredu" field should contain "manual"
        And The upgrade setting for "block_annotate" should be "manual"
        And The upgrade setting for "local_aspiredu" should be "manual"

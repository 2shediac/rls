<?php

require_once(__DIR__.'/../../../../lib/behat/behat_base.php');

class behat_local_rlsiteadmin extends behat_base {

    /**
     * Opens dashboard.
     *
     * @Given /^I go to dashboard$/
     */
    public function i_go_to_dashboard() {
        $this->getSession()->visit($this->locate_path('/local/rlsiteadmin/dashboard/'));
    }

    /**
     * Opens mass.
     *
     * @Given /^I go to mass$/
     */
    public function i_go_to_mass() {
        $this->getSession()->visit($this->locate_path('/local/rlsiteadmin/mass/'));
    }

    /**
     * Load mass data.
     *
     * @Given /^I load mass data$/
     */
    public function i_load_mass_data() {
        global $CFG, $DB;
        require_once($CFG->dirroot.'/local/rlsiteadmin/lib.php');
        require_once($CFG->dirroot.'/local/rlsiteadmin/lib/data_cache.php');

        set_config('local_rlsiteadmin_manualupgradeplugins', 'auth_rladmin,auth_gauth');
        set_config('local_rlsiteadmin_autoupgradeplugins', 'block_course_appointments,block_elluminate');
        $cache = new local_rlsiteadmin_data_cache();
        foreach (['addonlist', 'grouplist'] as $type) {
            $newdata = json_decode(file_get_contents($CFG->dirroot.'/local/rlsiteadmin/tests/fixtures/'.$type.'.json'), true);
            // Set expiry to current time.
            $newdata['timestamp'] = time();
            $data = $cache->update_data($type, $newdata);
        }
    }

    /**
     * Check to see if setting is set for manual or automatic upgades for an addon.
     *
     * @When /^The upgrade setting for "([^"]*)" should be "([^"]*)"$/
     */
    public function the_upgrade_setting_for_should_be($addon, $setting) {
        global $DB;
        $result = $DB->get_field('config_plugins', 'value', ['plugin' => 'local_rlsiteadmin', 'name' => 'plugins_upgrademethod']);
        $addonsettings = json_decode($result, true);
        if (empty($addonsettings)) {
            throw new Exception("plugins_upgrademethod empty");
        }
        if (!is_array($addonsettings)) {
            $addonsettings = [];
        }
        if (empty($addonsettings[$addon])) {
            if ($setting == 'auto') {
                throw new Exception("Setting for {$addon} in plugins_upgrademethod is empty and defaults to manual.");
            }
            $addonsettings[$addon] = 'manual';
        }
        if ($addonsettings[$addon] !== $setting) {
            $message = "Setting for {$addon} in plugins_upgrademethod is not set to {$setting}";
            $mesage .= " currently set to {$addonsettings[$addon]}";
            throw new Exception($message);
        }
    }

    /**
     * Execulte upgrade task.
     *
     * @Given The upgrade plugins task is executed
     */
    public function run_upgrade_task() {
        $task = new \local_rlsiteadmin\mass\task\upgradeaddons();
        $task->execute();
    }

    /**
     * Check upgrade task.
     *
     * @Given The update plugins task has created mass dispatcher file
     */
    public function has_update_auto_event() {
        global $CFG;
        $dirfd = opendir($CFG->behat_dataroot.'/temp');
        while (($file = readdir($dirfd)) !== false) {
            if (preg_match("/^addon_/", $file)) {
                $command = file($CFG->behat_dataroot.'/temp/'.$file);
                if ($command[0] !== "site {$CFG->dirroot}\n") {
                    $message = "Site command has incorrect directory";
                    $message .= ", recieved '".$command[0]."' Expecting 'site {$CFG->dirroot}'";
                    throw new \Exception($message);
                }
                if ($command[1] !== "update local_aspiredu") {
                    throw new \Exception("update local_aspiredu command not present: ".$command[1]);
                }
            }
        }
    }

    /**
     * @When /^I check the "([^"]*)" radio button$/
     */
    public function i_check_the_radio_button($radio) {
        $radiobutton = $this->getSession()->getPage()->findField($radio);
        if (null === $radiobutton) {
            throw new Exception("Cannot find radio button ".$radio);
        }
        $this->getSession()->getDriver()->click($radiobutton->getXPath());
    }

    /**
     * Returns fixed step argument (with \\" replaced back to ")
     *
     * @param string $argument
     *
     * @return string
     */
    protected function fixstepargument($argument) {
        return str_replace('\\"', '"', $argument);
    }

    /**
     * Checks, that form field with specified id|name|label|value doesn't have specified value
     * Example: Then the "username" field should not contain "batman"
     * Example: And the "username" field should not contain "batman"
     *
     * @Then /^the "(?P<field>(?:[^"]|\\")*)" field should not contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertfieldnotcontains($field, $value) {
        $field = $this->fixstepargument($field);
        $value = $this->fixstepargument($value);
        $this->assertSession()->fieldValueNotEquals($field, $value);
    }

    /**
     * Checks, that form field with specified id|name|label|value has specified value
     * Example: Then the "username" field should contain "bwayne"
     * Example: And the "username" field should contain "bwayne"
     *
     * @Then /^the "(?P<field>(?:[^"]|\\")*)" field should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertfieldcontains($field, $value) {
        $field = $this->fixstepargument($field);
        $value = $this->fixstepargument($value);
        $this->assertSession()->fieldValueEquals($field, $value);
    }

}

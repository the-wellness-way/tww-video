<?php

use TwwVideo\Admin\TwwvAdminMenu;

class Test_TwwvAdminMenu extends WP_UnitTestCase {
    /**
     * @covers TWW_CancelRoute::test_assert_instance_of_class
     * @group adminMenu
     */
    function test_assert_instance_of_class() { 
        $this->assertInstanceOf(TwwvAdminMenu::class, new TwwvAdminMenu());     
    }

    /**
     * @covers TWW_CancelRoute::test_prefix_is_twwv
     * @group adminMenu
     */
    public function test_prefix_is_twwv() {
        //Usually don't test contracts but we have a lot of prefix's in various plugins
        $twwvAdminMenu = new TwwvAdminMenu();
        $this->assertEquals("twwv__", $twwvAdminMenu->get_prefix());
    }

    /**
     * @covers TWW_CancelRoute::validate_key_char_count
     * @covers TWW_CancelRoute::validate_secret_char_count
     * @group adminMenu
     */
    public function test_valid_key_char_count() {
        $twwvAdminMenu = new TwwvAdminMenu();

        $credential = 'notvalid';
        $this->assertEquals(false, $twwvAdminMenu->validate_key_char_count($credential));
        $this->assertEquals(false, $twwvAdminMenu->validate_secret_char_count($credential));

        $credential = 'thisis20charslongxxx';
        $this->assertEquals(true, $twwvAdminMenu->validate_key_char_count($credential));

        $credential = 'thisis40charslongxxxthisis40charslongxxx';
        $this->assertEquals(true, $twwvAdminMenu->validate_secret_char_count($credential));
    }
}   
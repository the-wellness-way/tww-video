<div class="twwv-container wrap" id="tww-admin">

<div class="twwv--page-title-wrapper">
    <div class="twwv--page-header">
    <?php
        use TwwVideo\Admin\TwwvAdminMenu;

        $logo = TWWV_PLUGIN_URL . 'resources/images/twwlogo70.webp';
        echo '<h1>TWWV - Settings</h1>';
        settings_errors();
    ?>
    </div>
</div>

<div class="twwv_content_wrapper">
    <?php
        $page_indentifier = 'twwv-calculator';
        $settings_slug  = TwwvAdminMenu::get_settings_page();
        // $tab_two        =  TwwvAdminMenu::get_tab_two();

        $active_tab = isset( $_GET[ 'page' ] ) ? sanitize_text_field(wp_unslash($_GET[ 'page' ])) : $settings_slug;
    ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=twwv-video" class="nav-tab <?php echo ($active_tab == $settings_slug || $active_tab == $page_indentifier) ? 'nav-tab-active' : ''; ?>">Settings</a>
        <!-- <a href="?page=twwv-protein-calculator-settings" class="nav-tab <?php echo $active_tab == $tab_two ? 'nav-tab-active' : ''; ?>">Protein Calculator</a> -->
    </h2>

    <form method="post" action="options.php">
        <?php
            if( $active_tab === $settings_slug || $active_tab === $page_indentifier ) {
                settings_fields('twwv-common-settings-options');
                do_settings_sections($settings_slug);
                submit_button();
            } 
            
            // elseif( $active_tab === $tab_two ) {
            //     settings_fields('twwv-protein-calculator-options');
            //     do_settings_sections($tab_two);
            //     submit_button();
            // }
        ?>
    </form>
</div>
</div>

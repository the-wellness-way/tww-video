<?php
namespace TwwVideo\Admin;

use TwwVideo\Options\TwwvOptions;

class TwwvAdminMenu {
    const PAGE_IDENTIFIER = 'twwv-video';
    const PAGE_TEMPLATE = 'dashboard';

    const COMMON_SETTINGS_PAGE = 'twwv-common-settings';

    private $option_group = 'settings';

    private $option_name = 'settings';

    private $prefix;

    private $settings = [];

    public function __construct() {
        $settings = $this->get_settings();

        $this->prefix = $this->get_prefix();
        $this->settings = $settings ?? [];

        $this->option_group = $this->prefix . $this->option_group;
        $this->option_name  = $this->prefix . $this->option_name;
    }

    public function register_hooks(): void {
        add_action('admin_menu', [$this, 'register_pages']);
        add_action('admin_init', [$this, 'register_common_settings']);
    }

    public function register_pages(): void {
        $manage_capability = $this->get_manage_capability();
        $page_identifier = $this->get_page_identifier();

        $menu = add_menu_page(
            'TWW Video: ' . __('Dashboard', 'twwv-video'),
            'TWW Video',
            $manage_capability,
            $page_identifier,
            [$this, 'show_page'],
            'dashicons-video-alt',
            98
        );

        $submenu = add_submenu_page(
            $page_identifier,
            'TWW Video: ' . __('Settings', 'twwv-video'),
            __('Settings', 'twwv-video'),
            $manage_capability,
            self::COMMON_SETTINGS_PAGE,
            [$this, 'show_page'],
            1
        );


        add_action( 'load-' . $menu, [$this, 'do_admin_enqueue'] );
        add_action( 'load-' . $submenu, [$this, 'do_admin_enqueue'] );
    }

    public function do_admin_enqueue(): void {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'] );
    }

    public function enqueue_admin_scripts(): void {
        $version = '1.0.0';

        wp_enqueue_style('twwv-admin-js', TWWV_PLUGIN_URL . 'resources/css/twwv-admin.css', [], $version, 'all' );
        wp_enqueue_script('twwv-admin-js', TWWV_PLUGIN_URL . 'resources/js/twwv-admin.js', [], $version, true );
    }

    public function register_common_settings(): void {
        /**
         * Overall settings
         */
        add_settings_section(
            'twwv-common-settings-section',
            __('TWW Video Settings', 'twwv-video'),
            null,
            self::COMMON_SETTINGS_PAGE,
            []
        );

        add_settings_field(
            'twwv-aws-config',
            '<span class="required">*</span> '. __('S3 Client Credentials', 'twwv-video'),
            [$this, 'aws_config_callback'],
            self::COMMON_SETTINGS_PAGE,
            'twwv-common-settings-section',
            ['max' => 32]
        );

        register_setting('twwv-common-settings-options', $this->option_group, [$this, 'validate_common_settings']);
    }

    public function validate_common_settings($input): array {
        $valid_input = $this->settings;

        $iam_user = isset($input['aws_config']) && isset($input['aws_config']['iam_user']) ? $input['aws_config']['iam_user'] : '';
        $iam_role = isset($input['aws_config']) && isset($input['aws_config']['iam_role']) ? $input['aws_config']['iam_role'] : '';
        $iam_key = isset($input['aws_config']) && isset($input['aws_config']['iam_key']) ? $input['aws_config']['iam_key'] : '';
        $iam_secret = isset($input['aws_config']) && isset($input['aws_config']['iam_secret']) ? $input['aws_config']['iam_secret'] : '';

        if(! $this->validate_arn($iam_user)) {
            add_settings_error($this->prefix . 'arn', 'iam_error', 'IAM User ARN must be an AWS ARN value!', 'error');

            return $valid_input;
        }

        if(! $this->validate_arn($iam_role)) {
            add_settings_error($this->prefix . 'arn', 'iam_error', 'IAM Role ARN must be an AWS ARN value!', 'error');

            return $valid_input;
        }

        if(! $this->validate_key_char_count($iam_key)) {
            add_settings_error($this->prefix . 'char_count', 'iam_error', 'IAM Key must be 20 characters!', 'error');

            return $valid_input;
        }

        if(! $this->validate_secret_char_count($iam_secret)) {
            add_settings_error($this->prefix . 'char_count', 'iam_error', 'IAM Secret must be 40 characters!', 'error');

            return $valid_input;
        }

        return $input;
    }

    public function get_page_identifier(): string
    {
        return self::PAGE_IDENTIFIER;
    }

    public function get_manage_capability(): string
    {
        return 'manage_options';
    }

    public function show_page(): void
    {
        require_once TWWV_PLUGIN_PATH . 'pages/' . self::PAGE_TEMPLATE . '.php';
    }

    /**
     * Overall Settings
     * 
     * 
     */
    public function aws_config_callback(): void {
        $options = TwwvOptions::get_option($this->option_name);
        $iam_user = isset($options['aws_config']['iam_user']) ? $options['aws_config']['iam_user'] : '';
        $iam_role = isset($options['aws_config']['iam_role']) ? $options['aws_config']['iam_role'] : '';
        $iam_secret = isset($options['aws_config']['iam_secret']) ? $options['aws_config']['iam_secret'] : '';
        $iam_key = isset($options['aws_config']['iam_key']) ? $options['aws_config']['iam_key'] : '';

        echo "
        <div class='twwv__aws_configs'>
            <table>
                <tr>
                    <td>
                        <label><strong>IAM User Arn</strong></label>
                    </td>
                    <td>
                    <input style='width: 375px;' width='275px' placeholder='IAM User' id='aws_iam_user' class='twwv__aws-option' type='text' name='" . esc_attr($this->option_name) . "[aws_config][iam_user]' value='".esc_attr($iam_user)."' />
                    </td>
                </tr>
                <tr>
                    <td>
                    <label><strong>IAM Role Arn</strong></label>
                    </td>
                    <td>
                    <input style='width: 375px;' width='275px' placeholder='IAM Role' id='aws_iam_user' class='twwv__aws-option' type='text' name='" . esc_attr($this->option_name) . "[aws_config][iam_role]' value='".esc_attr($iam_role)."' />
                    </td>
                </tr>
                <tr>
                    <td>
                    <label><strong>Key</strong></label>
                    </td>
                    <td>
                    <input style='width: 375px;' width='275px' placeholder='IAM Key' id='aws_iam_user' class='twwv__aws-option' type='text' name='" . esc_attr($this->option_name) . "[aws_config][iam_key]' value='".esc_attr($iam_key)."' />
                    </td>
                </tr>
                <tr>
                    <td>
                    <label><strong>Secret</strong></label>
                    </td>
                    <td>
                    <input style='width: 375px;' width='275px' placeholder='IAM Secret' id='aws_iam_user' class='twwv__aws-option' type='text' name='" . esc_attr($this->option_name) . "[aws_config][iam_secret]' value='".esc_attr($iam_secret)."' />
                    </td>
                </tr>
            </table>
        </div>
        ";
    }

    public function validate_arn(string $arn): bool {
        return strpos($arn,"arn:") !== false;
    }

    public function validate_key_char_count(string $credential): bool {
        return strlen($credential) === 20;
    }

    public function validate_secret_char_count(string $credential): bool {
        return strlen($credential) === 40;
    }

    public function get_prefix() {
        return TwwvOptions::PREFIX;
    }
    
    public function get_settings() {
        return TwwvOptions::get_option($this->option_name, []);
    }

    public static function get_settings_page(): string {
        return self::COMMON_SETTINGS_PAGE;
    }
}
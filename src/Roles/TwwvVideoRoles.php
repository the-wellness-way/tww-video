<?php
namespace TwwVideo\Roles;

class TwwvVideoRoles {
    public function __construct() {
        add_action('init', [$this, 'add_roles']);
    }

    // Adding a custom role "Video Editor" with capabilities specific to "pp_video_block"
    public function add_roles() {
        add_role('video_editor', 'Video Editor', [
            'read' => true,
            'edit_pp_video_block' => true,
            'edit_others_pp_video_block' => true,
            'edit_published_pp_video_block' => true,
            'edit_private_pp_video_block' => true,
            'publish_pp_video_blocks' => true,
            'delete_pp_video_block' => true,
            'delete_others_pp_video_block' => true,
            'delete_published_pp_video_block' => true,
            'delete_private_pp_video_block' => true,
        ]);
    }
}

$twwvVideoRoles = new TwwvVideoRoles();
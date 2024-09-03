<?php

use TwwVideo\Import\TwwvImporter;

class Test_TwwvImporter extends WP_UnitTestCase {
    /**
     * @group Importer
     */
    public function test_assert_instance_of_class() { 
        $this->assertInstanceOf(TwwvImporter::class, new TwwvImporter());     
    }

    public function test_generate_s3_path_returns_vaild_path() {
        //Leaving full data incase of adding options, and for viewing reasons
        $post_data = array(
            'ID' => 123,
            // 'post_author' => 1,
            'post_date' => '2024-07-06 10:00:00',
            'post_date_gmt' => '2024-07-06 14:00:00',
            // 'post_content' => 'This is a sample post content.',
            // 'post_title' => 'Sample Post Title',
            // 'post_excerpt' => 'Sample post excerpt.',
            // 'post_status' => 'publish',
            // 'comment_status' => 'open',
            // 'ping_status' => 'open',
            'post_name' => 'sample-post-title',
            // 'post_modified' => '2024-07-06 10:00:00',
            // 'post_modified_gmt' => '2024-07-06 14:00:00',
            // 'post_content_filtered' => '',
            // 'post_parent' => 0,
            // 'guid' => 'http://example.com/?p=123',
            // 'menu_order' => 0,
            // 'post_type' => 'post',
            // 'post_mime_type' => '',
            // 'comment_count' => 0,
        );
    
        $post = new \WP_Post((object) $post_data);

        $importer = new TwwvImporter();

        $this->assertEquals('videos/adp/2024/July/sample-post-title.mp4', $importer->generate_s3_path($post));
    }
}   
<?php
namespace TwwVideo\Import;

use TwwVideo\Options\TwwvOptions;
use Aws\Sts\StsClient;
use Aws\S3\S3Client;
use Aws\S3\Exception\MultipartUploadException;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class TwwvImporter {
    const BUCKET_NAME = 'the-wellness-way';
    const REGION_NAME = 'us-east-1';
    const ROLE_SESSION_NAME = 'WordPressS3Session';

    private $s3Client = null;
    private $key = null;
    private $secret = null;
    private $iam_user = null;
    private $iam_role = null;
    private $settings = null;

    public function __construct() {
        $this->settings = TwwvOptions::get_option("settings", []) ?? null;
        
        $this->set_iam_user();
        $this->set_iam_role();
        $this->set_key();
        $this->set_secret();

        add_action("rest_api_init", [$this, "register_rest_routes"]);
    }

    public function filter_site_upload_size_limit() {
        return 64 * 1024 * 1024;
    }

    public function register_rest_routes() {
        $namespace = 'tww/v1';
        $route = '/do-import';

        register_rest_route($namespace, $route, [
            'methods' => [
                \WP_REST_Server::READABLE,
                \WP_REST_Server::CREATABLE
            ],
            'permission_callback' => '__return_true',
            'callback' => [$this, "do_import"]
        ]);
    }

    public function do_import(\WP_REST_Request $request) {
        $params = $request->get_params();
        $post_id = $params['post_id'] ?? null;
        $number = $params['posts_per_page'] ?? null;

        $posts = [];
        $vids = null;

        if ($post_id) {
            $post = get_post($post_id);
            $vids = [$post];
        } else {
            $query = new \WP_Query([
                "post_type" => 'pp_video_block',
                "posts_per_page" => $number ?? 1 
            ]);
            $vids = $query->posts;
        }

        if (count($vids) > 0) {
            foreach ($vids as $vid) {
                $parsed_blocks = \parse_blocks($vid->post_content);
                $block_data = $this->get_block_data($parsed_blocks);
                $src = $block_data[0]['src'];
                $year = date('Y', strtotime($vid->post_date));
                $month = date('F', strtotime($vid->post_date));
    
                if ('presto-player/self-hosted' === $block_data[0]['block_name']) {
                    $s3_url = $this->download_from_rumble_to_s3($vid, $src);
                    $posts[] = [
                        'ID' => $vid->ID,
                        'title' => $vid->post_title,
                        'attrs' => $parsed_blocks,
                        'year' => $year, 
                        'month' => $month, 
                        'block_data' => $block_data,
                        's3_url' => $s3_url
                    ];
                } else {
                    continue;
                }

                break;
            }
        }

        return rest_ensure_response($posts);
    }

    public function download_from_rumble_to_s3(\WP_Post $post = null, $src) {
        $this->s3Client = $this->createS3Client();
        $s3_bucket_key = $this->generate_s3_path($post);

        // Create a multipart upload
        try {
            $result = $this->s3Client->createMultipartUpload([
                'Bucket' => self::BUCKET_NAME,
                'Key' => $s3_bucket_key,
                'StorageClass' => 'STANDARD',
            ]);
            $uploadId = $result['UploadId'];
        } catch (AwsException $e) {
            throw new \Exception("Failed to create multipart upload: " . $e->getMessage());
        }

        $parts = [];
        $partNumber = 1;

        
        $success = $this->file_get_contents_chunked($src, 5 * 1024 * 1024, function($chunk, &$handle, $iteration) use ($uploadId, &$parts, &$partNumber, $s3_bucket_key) {
            error_log(print_r('partNumber', true));
            error_log(print_r($partNumber, true));
            error_log("Uploading part number $partNumber with size " . strlen($chunk));
            try {
                $result = $this->s3Client->uploadPart([
                    'Bucket' => self::BUCKET_NAME,
                    'Key' => $s3_bucket_key,
                    'UploadId' => $uploadId,
                    'PartNumber' => $partNumber,
                    'Body' => $chunk,
                ]);

                $parts[] = [
                    'PartNumber' => $partNumber,
                    'ETag' => $result['ETag'],
                ];

                $partNumber++;
            } catch (AwsException $e) {
                throw new \Exception("Failed to upload part: " . $e->getMessage());
            }
        });

        if (!$success) {
            try {
                $this->s3Client->abortMultipartUpload([
                    'Bucket' => self::BUCKET_NAME,
                    'Key' => $s3_bucket_key,
                    'UploadId' => $uploadId,
                ]);
            } catch (AwsException $e) {
                throw new \Exception("Failed to abort multipart upload: " . $e->getMessage());
            }
            throw new \Exception("File upload failed");
        }

        // Complete the multipart upload
        try {
            $result = $this->s3Client->completeMultipartUpload([
                'Bucket' => self::BUCKET_NAME,
                'Key' => $s3_bucket_key,
                'UploadId' => $uploadId,
                'MultipartUpload' => ['Parts' => $parts],
            ]);
            return $result['Location'];
        } catch (AwsException $e) {
            throw new \Exception("Failed to complete multipart upload: " . $e->getMessage());
        }
    }

    private function file_get_contents_chunked($file, $chunk_size, $callback) {
        try {
            $handle = fopen($file, "r");
            $i = 0;
    
            while (!feof($handle)) {
                $chunk = fread($handle, $chunk_size);
    
                if ($chunk === false) {
                    throw new \Exception("Failed to read file chunk");
                }
    
                error_log("Reading chunk number $i with size " . strlen($chunk));
    
                // Upload the chunk
                call_user_func_array($callback, [$chunk, &$handle, $i]);
    
                $i++;
            }
            fclose($handle);
        } catch (Exception $e) {
            trigger_error("file_get_contents_chunked::" . $e->getMessage(), E_USER_NOTICE);
            return false;
        }
        return true;
    }

    private function get_block_data($blocks) {
        $data = [];

        foreach ($blocks as $block) {
            if (isset($block['blockName']) && isset($block['attrs']['src'])) {
                $data[] = [
                    'block_name' => $block['blockName'],
                    'src' => $block['attrs']['src']
                ];
            }

            if (!empty($block['innerBlocks'])) {
                $inner_data = $this->get_block_data($block['innerBlocks']);
                $data = array_merge($data, $inner_data);
            }
        }

        return $data;
    }

    private function createS3Client() {
        $region = $this->get_region();
        $key = $this->get_key();
        $secret = $this->get_secret();
        $iam_role = $this->get_iam_role();
        $role_session_name = $this->get_role_session_name();

        $stsClient = new StsClient([
            'region' => $region, 
            'version' => 'latest',
            'credentials' => new Credentials(
                $key,
                $secret
            ),
        ]);

        try {
            $result = $stsClient->assumeRole([
                'RoleArn' => $iam_role,
                'RoleSessionName' => $role_session_name,
            ]);

            $credentials = $result['Credentials'];

            return new S3Client([
                'region' => $region, 
                'version' => 'latest',
                'credentials' => new Credentials(
                    $credentials['AccessKeyId'],
                    $credentials['SecretAccessKey'],
                    $credentials['SessionToken']
                ),
            ]);
        } catch (AwsException $e) {
            throw new \Exception("Failed to assume role: " . $e->getMessage());
        }
    }

    public function generate_s3_path(\WP_Post $post = null) {
        if ($post) {
            $month = date('F', strtotime($post->post_date)); // Capitalized month like June
            $year = date('Y', strtotime($post->post_date));  // Four-digit year like 2024
            $slug = $post->post_name;
    
            return "videos/adp/{$year}/{$month}/{$slug}.mp4";
        } 
    
        return null;
    }

    private function set_iam_user() {
        if($this->settings && isset($this->settings['aws_config']) && isset($this->settings['aws_config']['iam_user'])) {
            $this->iam_user = $this->settings['aws_config']['iam_user'];
        }  
    }
    private function set_iam_role() {
        if($this->settings && isset($this->settings['aws_config']) && isset($this->settings['aws_config']['iam_role'])) {
            $this->iam_role = $this->settings['aws_config']['iam_role'];
        }  
    }

    private function set_key() {
        if($this->settings && isset($this->settings['aws_config']) && isset($this->settings['aws_config']['iam_key'])) {
            $this->key = $this->settings['aws_config']['iam_key'];
        }  
    }
    private function set_secret() {
        if($this->settings && isset($this->settings['aws_config']) && isset($this->settings['aws_config']['iam_secret'])) {
            $this->secret = $this->settings['aws_config']['iam_secret'];
        }  
    }

    public function get_iam_user() {
        return $this->iam_user;
    }
    public function get_iam_role() {
        return $this->iam_role;  
    }

    public function get_key() {
        return $this->key;
    }
    public function get_secret() {
        return $this->secret;  
    }

    public function get_region() {
        return self::REGION_NAME;
    }

    public function get_bucket() {
        return self::BUCKET_NAME;
    }

    public function get_role_session_name() {
        return self::ROLE_SESSION_NAME;
    }
}

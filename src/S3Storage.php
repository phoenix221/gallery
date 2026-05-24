<?php

namespace App;

use Aws\S3\S3Client;
use Exception;

class S3Storage
{
    private S3Client $s3;
    private string $bucket;

    public function __construct()
    {
        $config = require './config.php';
        $this->bucket = $config['s3']['S3_BUCKET'];

        $this->s3 = new S3Client([
            'version' => 'latest',
            'region'  => $config['s3']['S3_REGION'],
            'endpoint' => $config['s3']['S3_URL'],
            'use_path_style_endpoint' => true,
            'credentials' => [
                'key'    => $config['s3']['S3_USER'],
                'secret' => $config['s3']['S3_PASSWORD'],
            ],
        ]);
    }

    public function upload(array $file): string
    {
        if (empty($file)){
            throw new Exception("Data is empty");
        }

        $result = $this->s3->putObject([
            'Bucket' => $this->bucket,
            'Key' => 'uploads/' . $file['name'],
            'Body' => fopen($file['tmp_name'], 'rb'),
            'ContentType' => $file['type'],
        ]);

        return $result['ObjectURL'];
    }

    public function delete(string $key): void
    {
        $this->s3->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $key,
        ]);
    }
}
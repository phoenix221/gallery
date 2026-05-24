<?php

namespace App;

use App\Database;
use App\S3Storage;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;

class ImageService
{
    public function uploadImage(string $filePath): array
    {
        $log = new Logger('Upload Image');
        $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
        $db = new Database();
        $s3 = new S3Storage();

        if (!file_exists($filePath)) {
            throw new Exception("File not found");
        }

        $fileType = mime_content_type(
            $filePath
        );
        $fileSize = filesize($filePath);
        switch ($fileType) {
            case 'image/jpeg':
                $type = 'jpeg';
                break;
            case 'image/png':
                $type = 'png';
                break;
            case 'image/gif':
                $type = 'gif';
                break;
        }
        $fileName = uniqid(). '.' .$type;

        $file = array(
            'name' => $fileName,
            'tmp_name' => $filePath,
            'size' => $fileSize,
            'type' => $fileType
        );

        try{
            $s3_url = $s3->upload($file);
        } catch (Exception $e){
            $log->error($e->getMessage());
        }

        $sql = "INSERT INTO `images` (`filename`, `original_name`, `size`, `mime_type`, `uploaded_at`, `s3_url`) VALUES (?, ?, ?, ?, ?, ?)";
        $params = array($fileName, $fileName, $fileSize, $fileType, date('Y-m-d H:i:s'), $s3_url);

        $db->query($sql, $params, 'INSERT');

        $result = array(
            'fileName' => $fileName,
            'originalName' => $fileName,
            'size' => $fileSize,
            'mimeType' => $fileType,
            'uploaded_at' => date('Y-m-d H:i:s'),
            's3_url' => $s3_url
        );

        return $result;
    }

    public function deleteImage(int $id): bool
    {
        $log = new Logger('Delete Image');
        $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
        $db = new Database();
        $s3 = new S3Storage();

        $sql = "SELECT * FROM `images` WHERE `id` = ? LIMIT 1";
        $params = array($id);

        try{
            $result = $db->query($sql, $params, 'SELECT');

            if(!empty($result)){
                $key = "/uploads/" . $result[0]['filename'];
                $s3->delete($key);

                $sql = "DELETE FROM `images` WHERE `id` = ?";
                $db->query($sql, $params, 'DELETE');
                return true;
            }
        }catch(Exception $e){
            $log->error($e->getMessage());
        }

        return false;
    }

    public function getImages(int $limit = 10, int $offset = 0): array
    {
        $log = new Logger('getImages');
        $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
        $db = new Database();

        $sql = "SELECT * FROM `images` LIMIT ? OFFSET ?";
        $params = array($limit, $offset);

        try{
            $result = $db->query($sql, $params, 'SELECT');
            if(!empty($result)){
                return $result;
            }

        }catch(Exception $e){
            $log->error($e->getMessage());
        }

        return [];
    }

    public function getImageById(int $id): ?array
    {
        $log = new Logger('getImageById');
        $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
        $db = new Database();

        $sql = "SELECT * FROM `images` WHERE `id` = ? LIMIT 1";
        $params = array($id);

        try{
            $result = $db->query($sql, $params, 'SELECT');
            if(!empty($result)){
                return $result[0];
            }

        }catch(Exception $e){
            $log->error($e->getMessage());
        }

        return null;
    }
}
<?php

namespace App;

use App\ImageService;
use Exception;
use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class ImageController
{
    public function index()
    {
        require './views/index.php';
    }
    public function getAllImages():void
    {
        $imageService = new ImageService();
        if (isset($_GET['limit']) && !is_numeric($_GET['limit'])) {
            http_response_code(400);
            echo json_encode([
                'error' => 'The limit parameter is incorrectly specified'
            ]);
            exit();
        }
        if (isset($_GET['offset']) && !is_numeric($_GET['offset'])) {
            http_response_code(400);
            echo json_encode([
                'error' => 'The offset parameter is incorrectly specified'
            ]);
            exit();
        }

        $limit = (int)$_GET['limit'] ?? 10;
        $offset = (int)$_GET['offset'] ?? 0;

        try{
            $images = $imageService->getImages($limit, $offset);
            if(!empty($images)){
                http_response_code(200);
                echo json_encode([
                    'success' => 'ok',
                    'data' => $images
                ]);
                exit();
            }
        }catch(Exception $e){
            $log = new Logger('GET ALL IMAGES');
            $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
            $log->error($e->getMessage());
        }
    }

    public function getImage(int $id)
    {
        if(!is_numeric($id)){
            http_response_code(400);
            echo json_encode([
                'error' => 'Id is not a number'
            ]);
            exit();
        }
        $imageService = new ImageService();
        try{
            $response = $imageService->getImageById($id);
            if($response){
                http_response_code(201);
                echo json_encode([
                    'success' => 'Ok',
                    'data' => $response
                ]);
                exit();
            }
        }catch (Exception $e){
            $log = new Logger('GET IMAGE BY ID');
            $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
            $log->error($e->getMessage());
        }
    }

    public function storeImage()
    {
        // пустая картинка
        if (empty($_FILES) || $_FILES['image']['size'] == 0){
            http_response_code(400);
            echo json_encode([
                'error' => 'The image is empty or not loaded.'
            ]);
            exit();
        }

        // проверка на размер картинки
        if ($_FILES['image']['size'] > 5242880){
            http_response_code(400);
            echo json_encode([
                'error' => 'The image size exceeds 5 MB'
            ]);
            exit();
        }

        // проверка на тип картинки
        $allowerTypes = [
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        $imageType = mime_content_type(
            $_FILES['image']['tmp_name']
        );
        if (!in_array($imageType, $allowerTypes)){
            http_response_code(400);
            echo json_encode([
                'error' => 'Invalid image format'
            ]);
            exit();
        }
        $image = $_FILES['image'];

        $imageService = new ImageService();

        try{
            $response = $imageService->uploadImage($image['tmp_name']);
            http_response_code(201);
            echo json_encode([
                'success' => 'Ok',
                'data' => $response
            ]);
            exit();
        }catch (Exception $e){
            $log = new Logger('STORE IMAGE');
            $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
            $log->error($e->getMessage());
        }

    }

    public function deleteImage(int $id)
    {
        if(!is_numeric($id)){
            http_response_code(400);
            echo json_encode([
                'error' => 'Id is not a number'
            ]);
            exit();
        }
        $imageService = new ImageService();
        try{
            $response = $imageService->deleteImage($id);
            if($response){
                http_response_code(201);
                echo json_encode([
                    'success' => 'Ok',
                ]);
                exit();
            }
        }catch (Exception $e){
            $log = new Logger('DELETE IMAGE');
            $log->pushHandler(new StreamHandler('logs/errors.log', Level::Error));
            $log->error($e->getMessage());
        }
    }
}
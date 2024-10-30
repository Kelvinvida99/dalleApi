<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Client\ApiClient;
use Service\DalleImageGenerationService;
use Controller\ImageController;
use Repository\ImageRepository;

// Cargar la configuración
$config = require __DIR__ . '/../src/config/config.php';

// Conexión a la base de datos
$pdo = new PDO('mysql:host=localhost;dbname=dalle', 'root', '');

// Instanciar el cliente, el servicio, el repositorio y el controlador
$client = new ApiClient($config['api_key']);
$imageService = new DalleImageGenerationService($client, $config['api_url']);
$imageRepository = new ImageRepository($pdo, $imageService);
$controller = new ImageController($imageService, $imageRepository);

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Leer el cuerpo de la solicitud JSON
    $requestBody = file_get_contents('php://input');
    $requestData = json_decode($requestBody, true);

    if (isset($requestData['prompt'])) {
        // Crear una nueva imagen si el "prompt" está presente
        $prompt = $requestData['prompt'];
        
        // Llamar al controlador para crear una imagen
        $response = $controller->handleRequest($requestData);
        echo json_encode($response);

    } elseif (isset($_GET['id']) && isset($_FILES['image'])) {
        // Actualizar una imagen existente si "id" e "image" están presentes
        $id = $_GET['id'];
        $image = $_FILES['image'];

        // Llamar al controlador para actualizar la imagen
        $response = $controller->updateImagesController($id, $image);
        echo json_encode($response);

    } else {
        // Error si faltan parámetros necesarios
        http_response_code(400);
        if (!isset($requestData['prompt'])) {
            echo json_encode(['error' => 'El parámetro "prompt" es obligatorio para crear una imagen.']);
        } elseif (!isset($_POST['id']) || !isset($_FILES['image'])) {
            echo json_encode(['error' => 'Los parámetros "id" y "image" son obligatorios para actualizar una imagen.']);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Llamar al controlador para obtener imágenes
    $images = $controller->getImages();
    echo json_encode($images);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
}

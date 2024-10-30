<?php

namespace Service;

use Client\ApiClientInterface;

class DalleImageGenerationService implements ImageGenerationServiceInterface
{
    private ApiClientInterface $client;
    private string $apiUrl;

    public function __construct(ApiClientInterface $client, string $apiUrl)
    {
        $this->client = $client;
        $this->apiUrl = $apiUrl;
    }


    public function updateImages($id, $image): array
    {
        $savePath = __DIR__ . "/../img/"; // Ruta relativa al archivo actual

        // Crear el directorio si no existe
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }

        // Determinar la extensión del archivo original y construir el nuevo nombre de archivo
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $fileName = $id . '.' . $extension;
        $filePath = $savePath . $fileName;

        // Mover el archivo subido al directorio de destino
        if (!move_uploaded_file($image['tmp_name'], $filePath)) {
            throw new \Exception('No se pudo guardar la imagen en el servidor.');
        }

        return [
            'id' => $id,
            'image' => $filePath
        ];
    }

    public function generateImage(string $prompt): array
    {
        $savePath = __DIR__ . "/../img/"; // Ruta relativa al archivo actual

        // Crear el directorio si no existe
        if (!is_dir($savePath)) {
            mkdir($savePath, 0777, true);
        }


        $data = ['prompt' => $prompt . " con fondo blanco, centrada, fotografia profesional", 'n' => 1, 'size' => '256x256'];
        $response = $this->client->post($this->apiUrl, $data);

        if (!isset($response['data'][0]['url'])) {
            throw new \Exception('No se pudo generar la imagen.');
        }

        $imageUrl = $response['data'][0]['url'];
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            throw new \Exception('La URL de la imagen no es válida.');
        }

        // Descargar la imagen
        $imageContent = file_get_contents($imageUrl);
        if ($imageContent === false) {
            throw new \Exception('Error al descargar la imagen.');
        }

        // Guardar la imagen en el directorio de la aplicación
        $filePath = $savePath . $prompt . '.png';
        $bytesWritten = file_put_contents($filePath, $imageContent);

        if ($bytesWritten === false) {
            throw new \Exception('No se pudo guardar la imagen en el servidor.');
        }

        return [
            'prompt' => $prompt,
            'image' => $filePath
        ];
    }
}

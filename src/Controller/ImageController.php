<?php
namespace Controller;

use Service\ImageGenerationServiceInterface;
use Repository\ImageRepository;

class ImageController {
    private ImageGenerationServiceInterface $imageService;
    private ImageRepository $imageRepository;

    public function __construct(ImageGenerationServiceInterface $imageService, ImageRepository $imageRepository) {
        $this->imageService = $imageService;
        $this->imageRepository = $imageRepository;
    }

    public function handleRequest(array $request): array {
        $prompt = $request['prompt'] ?? '';
        $imageData = $this->imageService->generateImage($prompt);

        // Guardar en la base de datos
        $this->imageRepository->saveImage($imageData['prompt'], $imageData['image']);

        return ['message' => 'Imagen generada y guardada exitosamente en la base de datos.'];
    }

    public function updateImagesController($id, $image): array {
        $imageData = $this->imageService->updateImages($id, $image);

        // Guardar en la base de datos
        $this->imageRepository->updateImage($imageData['id'], $imageData['image']);

        return ['message' => 'Imagen generada y guardada exitosamente en la base de datos.'];
    }

    public function getImages(): array {
        $images = $this->imageRepository->fillMissingImages();
        return $images;
    }
}

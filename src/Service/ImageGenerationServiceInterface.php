<?php
namespace Service;

interface ImageGenerationServiceInterface {
    public function generateImage(string $prompt): array;
    public function updateImages($id, $image): array;
}

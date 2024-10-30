<?php
namespace Repository;

use Service\ImageGenerationServiceInterface;

class ImageRepository {
    private ImageGenerationServiceInterface $imageService;
    private \PDO $db;

    public function __construct(\PDO $db, ImageGenerationServiceInterface $imageService) {
        $this->db = $db;
        $this->imageService = $imageService;
    }

    public function saveImage(string $prompt, string $imageContent): bool {
        $stmt = $this->db->prepare("INSERT INTO images (prompt, image) VALUES (:prompt, :image)");
        return $stmt->execute([
            ':prompt' => $prompt,
            ':image' => $imageContent
        ]);
    }

    public function updateImage(string $id, string $imageContent): bool {
        $stmt = $this->db->prepare("UPDATE images SET image = :image WHERE id = :id");
        return $stmt->execute([
            ':image' => $imageContent,
            ':id' => $id
        ]);
    }

    public function getAllImages(): array {
        $stmt = $this->db->query("SELECT id, prompt, image, created_at FROM images");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fillMissingImages() {
        $images = $this->getAllImages();
    
        foreach ($images as $image) {
            if (empty($image['image']) || is_null($image['image'])) {
                // Usar $this->imageService para llamar a generateImage
                $generatedImage = $this->imageService->generateImage($image['prompt']);
                $this->updateImage($image['id'], $generatedImage['image']); // Guarda la URL generada en la BD
            }
        }

        return $this->getAllImages();
    }
}


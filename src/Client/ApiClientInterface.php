<?php
namespace Client;

interface ApiClientInterface {
    public function post(string $url, array $data): array;
}

<?php

namespace App\Message;

final class VideoUploadMessage
{
    private $id;
    private string $path;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->path = $data['path'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPath() {
        return $this->path;
    }
}

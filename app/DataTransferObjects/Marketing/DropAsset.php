<?php

declare(strict_types=1);

namespace App\DataTransferObjects\Marketing;

final readonly class DropAsset
{
    public function __construct(
        public string $id,
        public string $kind,
        public string $url,
        public string $filename,
        public string $mime,
        public ?int $sizeBytes = null,
        public ?int $width = null,
        public ?int $height = null,
        public ?string $role = null,
        public ?array $linkedTo = null,
        public ?string $caption = null,
        public ?int $order = null,
        public ?string $notes = null,
        public ?string $uploadedAt = null,
        public ?int $uploadedById = null,
    ) {}

    public static function fromArray(array $a): self
    {
        return new self(
            id:           $a['id'],
            kind:         $a['kind'],
            url:          $a['url'],
            filename:     $a['filename'],
            mime:         $a['mime'],
            sizeBytes:    $a['size_bytes']     ?? null,
            width:        $a['width']          ?? null,
            height:       $a['height']         ?? null,
            role:         $a['role']           ?? null,
            linkedTo:     $a['linked_to']      ?? null,
            caption:      $a['caption']        ?? null,
            order:        $a['order']          ?? null,
            notes:        $a['notes']          ?? null,
            uploadedAt:   $a['uploaded_at']    ?? null,
            uploadedById: $a['uploaded_by_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id'              => $this->id,
            'kind'            => $this->kind,
            'url'             => $this->url,
            'filename'        => $this->filename,
            'mime'            => $this->mime,
            'size_bytes'      => $this->sizeBytes,
            'width'           => $this->width,
            'height'          => $this->height,
            'role'            => $this->role,
            'linked_to'       => $this->linkedTo,
            'caption'         => $this->caption,
            'order'           => $this->order,
            'notes'           => $this->notes,
            'uploaded_at'     => $this->uploadedAt,
            'uploaded_by_id'  => $this->uploadedById,
        ], fn ($v) => $v !== null);
    }
}

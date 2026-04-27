<?php

declare(strict_types=1);

namespace App\Services\Marketing;

use App\Exceptions\Marketing\InvalidDropSchema;
use Opis\JsonSchema\Validator;

final class DropSchemaValidator
{
    public function __construct(
        private readonly string $schemaPath = '',
    ) {}

    public function validate(array $payload, string $version = 'coach_drop_v1'): void
    {
        $path = $this->schemaPath ?: base_path("schemas/{$version}.schema.json");

        if (!is_file($path)) {
            throw new \RuntimeException("Schema file not found: {$path}");
        }

        $validator = new Validator();
        $result    = $validator->validate(
            json_decode(json_encode($payload)),
            file_get_contents($path)
        );

        if ($result->isValid()) {
            return;
        }

        $errors = [];
        $this->collect($result->error(), $errors);

        throw new InvalidDropSchema($errors);
    }

    private function collect(mixed $error, array &$out): void
    {
        if ($error === null) {
            return;
        }

        $path  = '/' . implode('/', $error->data()->fullPath());
        $out[] = ['path' => $path, 'message' => $error->message()];

        foreach ($error->subErrors() as $sub) {
            $this->collect($sub, $out);
        }
    }
}

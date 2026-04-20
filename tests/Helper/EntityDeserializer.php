<?php

namespace App\Tests\Helper;

use Symfony\Component\Serializer\SerializerInterface;

class EntityDeserializer
{
    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function fromArray(array $data, string $class, array $groups = [], array $serializerOptions = []): object
    {
        $entity = $this->serializer->denormalize($data, $class, 'json', [
            'groups' => $groups,
            ...$serializerOptions,
        ]);

        $ref = new \ReflectionClass($entity);
        foreach ($data as $key => $value) {
            if (!$ref->hasProperty($key)) {
                continue;
            }
            $prop = $ref->getProperty($key);
            $prop->setAccessible(true);

            // nur anfassen wenn noch nicht initialisiert
            if ($prop->isInitialized($entity)) {
                continue;
            }

            $type = $prop->getType()?->getName();
            $value = match (true) {
                $type === \DateTimeImmutable::class   => new \DateTimeImmutable($value),
                $type === \DateTime::class            => new \DateTime($value),
            };

            $prop->setValue($entity, $value);
        }

        return $entity;
    }
}

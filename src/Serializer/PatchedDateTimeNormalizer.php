<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;


class PatchedDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private const FORMAT_KEY = 'datetime_format';
    private const TIMEZONE_KEY = 'datetime_timezone';

    private array $defaultContext = [
        self::FORMAT_KEY => \DateTimeInterface::RFC3339,
        self::TIMEZONE_KEY => null,
    ];

    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): string
    {
        if (!$object instanceof \DateTimeInterface) {
            throw new \InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone = $this->getTimezone($context);

        if (null !== $timezone) {
            $object = clone $object;
            $object = $object->setTimezone($timezone);
        }

        return $object->format($dateTimeFormat);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof \DateTimeInterface;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            \DateTimeInterface::class => true,
            \DateTimeImmutable::class => true,
            \DateTime::class => true,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $dateTimeFormat = $context[self::FORMAT_KEY] ?? null;
        $timezone = $this->getTimezone($context);

        if (!is_string($data) || '' === trim($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                'Expected a valid date string.',
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
                true
            );
        }

        if (null !== $dateTimeFormat) {
            $object = \DateTime::class === $type
                ? \DateTime::createFromFormat($dateTimeFormat, $data, $timezone)
                : \DateTimeImmutable::createFromFormat($dateTimeFormat, $data, $timezone);

            if (false !== $object) {
                return $object;
            }
        }

        try {
            return \DateTime::class === $type ? new \DateTime($data, $timezone) : new \DateTimeImmutable($data, $timezone);
        } catch (\Exception $e) {
            if ($context['disable_type_enforcement'] ?? false) {
                return $data;
            }

            throw NotNormalizableValueException::createForUnexpectedDataType(
                $e->getMessage(),
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
                false,
                $e->getCode(),
                $e
            );
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return \in_array($type, [\DateTime::class, \DateTimeImmutable::class, \DateTimeInterface::class], true);
    }

    private function getTimezone(array $context): ?\DateTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if (null === $dateTimeZone) {
            return null;
        }

        return $dateTimeZone instanceof \DateTimeZone ? $dateTimeZone : new \DateTimeZone($dateTimeZone);
    }
}
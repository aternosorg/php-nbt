<?php

namespace Aternos\Nbt\Tag;

use Aternos\Nbt\Deserializer\DeserializerReadResult;

trait RawValueTag
{
    protected ?string $rawValue = null;
    protected ?int $rawValueType = null;

    /**
     * @return string|null
     */
    public function getRawValue(): ?string
    {
        return $this->rawValue;
    }

    /**
     * @param string|null $rawValue
     */
    public function setRawValue(?string $rawValue): void
    {
        $this->rawValueType = null;
        $this->rawValue = $rawValue;
    }

    protected function resetRawValue(): void
    {
        $this->rawValue = null;
        $this->rawValueType = null;
    }

    /**
     * @param int $format
     * @return bool
     */
    protected function rawValueValid(int $format): bool
    {
        return !is_null($this->rawValue) && (is_null($this->rawValueType) || $this->rawValueType === $format);
    }

    /**
     * @param DeserializerReadResult $result
     * @param int $format
     * @return void
     */
    protected function setRawDataFromSerializer(DeserializerReadResult $result, int $format): void
    {
        $this->rawValue = $result->getRawData();
        $this->rawValueType = $format;
    }
}

<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

/**
 * Options to read parts of an NBT structure raw (as unparsed bytes) to save time and memory
 *
 * Paths are tag names joined by "/", starting with the (usually empty) name of the root tag, e.g. "/Level/Entities".
 */
class TagOptions
{
    /**
     * @var list<string>
     */
    protected array $rawCompoundPaths = [];

    /**
     * @var list<string>|null
     */
    protected ?array $parsedCompoundPaths = null;

    protected ?int $maxListTagLength = null;

    /**
     * Compound tags at these paths are read raw
     *
     * @param list<string> $rawCompoundPaths
     */
    public function setRawCompoundPaths(array $rawCompoundPaths): static
    {
        $this->rawCompoundPaths = $rawCompoundPaths;
        return $this;
    }

    /**
     * @return list<string>
     */
    public function getRawCompoundPaths(): array
    {
        return $this->rawCompoundPaths;
    }

    /**
     * If set, only compound tags at these paths are parsed and all other compound tags (except the root) are read raw
     *
     * @param list<string>|null $parsedCompoundPaths
     */
    public function setParsedCompoundPaths(?array $parsedCompoundPaths): static
    {
        $this->parsedCompoundPaths = $parsedCompoundPaths;
        return $this;
    }

    /**
     * @return list<string>|null
     */
    public function getParsedCompoundPaths(): ?array
    {
        return $this->parsedCompoundPaths;
    }

    public function shouldBeReadRaw(Tag $tag): bool
    {
        $path = $tag->getStringPath();
        if ($path === null || $path === "") {
            return false;
        }
        if (in_array($path, $this->rawCompoundPaths, true)) {
            return true;
        }
        if ($this->parsedCompoundPaths !== null && $this->parsedCompoundPaths !== []) {
            return !in_array($path, $this->parsedCompoundPaths, true);
        }
        return false;
    }

    /**
     * List tags with more entries than this are read raw, null for no limit
     */
    public function setMaxListTagLength(?int $maxListTagLength): static
    {
        $this->maxListTagLength = $maxListTagLength;
        return $this;
    }

    public function getMaxListTagLength(): ?int
    {
        return $this->maxListTagLength;
    }
}

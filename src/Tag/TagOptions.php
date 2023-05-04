<?php

namespace Aternos\Nbt\Tag;

class TagOptions
{
    /**
     * @var string[]
     */
    protected array $rawCompoundPaths = [];

    /**
     * @var string[]|null
     */
    protected ?array $parsedCompoundPaths = null;

    /**
     * @var int|null
     */
    protected ?int $maxListTagLength = null;

    /**
     * @param string[] $rawCompoundPaths
     * @return $this
     */
    public function setRawCompoundPaths(array $rawCompoundPaths): static
    {
        $this->rawCompoundPaths = $rawCompoundPaths;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getRawCompoundPaths(): array
    {
        return $this->rawCompoundPaths;
    }

    /**
     * @param string[]|null $parsedCompoundPaths
     * @return $this
     */
    public function setParsedCompoundPaths(?array $parsedCompoundPaths): static
    {
        $this->parsedCompoundPaths = $parsedCompoundPaths;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getParsedCompoundPaths(): ?array
    {
        return $this->parsedCompoundPaths;
    }

    /**
     * @param Tag $tag
     * @return bool
     */
    public function shouldBeReadRaw(Tag $tag): bool
    {
        $path = $tag->getStringPath();
        if($path === "") {
            return false;
        }
        if(in_array($path, $this->getRawCompoundPaths(), true)) {
            return true;
        }
        $whitelist = $this->getParsedCompoundPaths();
        if($whitelist) {
            return !in_array($path, $whitelist, true);
        }
        return false;
    }

    /**
     * @param int|null $maxListTagLength
     * @return $this
     */
    public function setMaxListTagLength(?int $maxListTagLength): static
    {
        $this->maxListTagLength = $maxListTagLength;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMaxListTagLength(): ?int
    {
        return $this->maxListTagLength;
    }
}

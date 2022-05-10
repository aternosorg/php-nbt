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
     * @return TagOptions
     */
    public function setParsedCompoundPaths(?array $parsedCompoundPaths): TagOptions
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
     * @param CompoundTag $tag
     * @return bool
     */
    public function shouldBeReadRaw(CompoundTag $tag): bool
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
}

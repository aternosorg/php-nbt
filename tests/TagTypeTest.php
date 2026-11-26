<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\ByteTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\DoubleTag;
use Aternos\Nbt\Tag\EndTag;
use Aternos\Nbt\Tag\FloatTag;
use Aternos\Nbt\Tag\IntArrayTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\ListTag;
use Aternos\Nbt\Tag\LongArrayTag;
use Aternos\Nbt\Tag\LongTag;
use Aternos\Nbt\Tag\ShortTag;
use Aternos\Nbt\Tag\StringTag;
use Aternos\Nbt\Tag\Tag;
use Aternos\Nbt\Tag\TagType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TagTypeTest extends TestCase
{
    /**
     * Each case is: type, type id in NBT data, name, tag class
     */
    public static function tagTypeProvider(): iterable
    {
        yield "end" => [TagType::END, 0, "TAG_End", EndTag::class];
        yield "byte" => [TagType::BYTE, 1, "TAG_Byte", ByteTag::class];
        yield "short" => [TagType::SHORT, 2, "TAG_Short", ShortTag::class];
        yield "int" => [TagType::INT, 3, "TAG_Int", IntTag::class];
        yield "long" => [TagType::LONG, 4, "TAG_Long", LongTag::class];
        yield "float" => [TagType::FLOAT, 5, "TAG_Float", FloatTag::class];
        yield "double" => [TagType::DOUBLE, 6, "TAG_Double", DoubleTag::class];
        yield "byte array" => [TagType::BYTE_ARRAY, 7, "TAG_Byte_Array", ByteArrayTag::class];
        yield "string" => [TagType::STRING, 8, "TAG_String", StringTag::class];
        yield "list" => [TagType::LIST, 9, "TAG_List", ListTag::class];
        yield "compound" => [TagType::COMPOUND, 10, "TAG_Compound", CompoundTag::class];
        yield "int array" => [TagType::INT_ARRAY, 11, "TAG_Int_Array", IntArrayTag::class];
        yield "long array" => [TagType::LONG_ARRAY, 12, "TAG_Long_Array", LongArrayTag::class];
    }

    /**
     * @param class-string<Tag> $tagClass
     */
    #[DataProvider("tagTypeProvider")]
    public function testTagTypeMatchesIdNameAndClass(TagType $type, int $id, string $name, string $tagClass): void
    {
        $this->assertSame($id, $type->value);
        $this->assertSame($name, $type->getName());
        $this->assertSame($tagClass, $type->getTagClass());
        $this->assertSame($type, $tagClass::TYPE);
        $this->assertSame($type, new $tagClass()->getType());
    }

    public function testEveryTagTypeIsCovered(): void
    {
        $this->assertCount(count(TagType::cases()), iterator_to_array(self::tagTypeProvider()));
    }

    public function testListContentTypeIsReadAsTagType(): void
    {
        // Root list with an empty name (Bedrock Edition), content type TAG_Int, length 0
        $list = Tag::load(new StringReader("\x09\x00\x00\x03\x00\x00\x00\x00", NbtFormat::BEDROCK_EDITION));

        $this->assertInstanceOf(ListTag::class, $list);
        $this->assertSame(TagType::INT, $list->getContentType());
        $this->assertStringStartsWith("TAG_List<TAG_Int>('None')", (string)$list);
    }
}

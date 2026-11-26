<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tests;

use Aternos\Nbt\Exception\InvalidTagValueException;
use Aternos\Nbt\IO\Reader\StringReader;
use Aternos\Nbt\IO\Writer\StringWriter;
use Aternos\Nbt\NbtFormat;
use Aternos\Nbt\Tag\ByteArrayTag;
use Aternos\Nbt\Tag\CompoundTag;
use Aternos\Nbt\Tag\IntTag;
use Aternos\Nbt\Tag\LongArrayTag;
use Aternos\Nbt\Tag\Tag;
use Aternos\Nbt\Tag\TagOptions;
use Exception;
use PHPUnit\Framework\TestCase;

class RegressionTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testLongArrayInsideRawCompoundKeepsAllValues(): void
    {
        $longArray = new LongArrayTag();
        foreach ([1, 2, 3] as $value) {
            $longArray[] = $value;
        }
        $root = new CompoundTag()
            ->set("raw", new CompoundTag()->set("longs", $longArray))
            ->set("after", new IntTag()->setValue(42));

        foreach ([NbtFormat::JAVA_EDITION, NbtFormat::BEDROCK_EDITION, NbtFormat::BEDROCK_EDITION_NETWORK] as $format) {
            $writer = new StringWriter()->setFormat($format);
            $root->write($writer);
            $data = $writer->getStringData();

            $options = new TagOptions()->setRawCompoundPaths(["/raw"]);
            $loaded = Tag::load(new StringReader($data, $format), $options);
            $this->assertInstanceOf(CompoundTag::class, $loaded);
            $this->assertSame(42, $loaded->getInt("after")?->getValue());

            $rewriter = new StringWriter()->setFormat($format);
            $loaded->write($rewriter);
            $this->assertSame($data, $rewriter->getStringData());
        }
    }

    /**
     * @throws Exception
     */
    public function testCompoundWithDuplicateNamesCannotBeWritten(): void
    {
        $renamed = new IntTag()->setValue(2);
        $root = new CompoundTag()
            ->set("a", new IntTag()->setValue(1))
            ->set("b", $renamed);
        $renamed->setName("a");

        $this->expectException(InvalidTagValueException::class);
        $this->expectExceptionMessage("Duplicate key 'a'");
        $root->write(new StringWriter()->setFormat(NbtFormat::JAVA_EDITION));
    }

    public function testByteArrayStringShowsBytesAsTwoHexDigits(): void
    {
        $array = new ByteArrayTag()->setName("bytes");
        foreach ([0, 1, -1, 127, -128] as $value) {
            $array[] = $value;
        }
        $this->assertSame("TAG_Byte_Array('bytes'): 5 bytes [00 01 ff 7f 80]", strval($array));
    }
}

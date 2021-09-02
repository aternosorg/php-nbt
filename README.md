# php-nbt
A full PHP implementation of Minecraft's Named Binary Tag (NBT) format.

In contrast to other implementations, this library provides full support
for 64-bit types, including the relatively new `TAG_Long_Array`.

Additionally, all three flavors (Java Edition, Bedrock Edition/little endian, and Bedrock Edition/VarInt) of the NBT format are supported.

## Usage
### Reading NBT data
To read existing NBT data, a Reader object is required.
This library implements different readers to read NBT data from strings.
```php
//Read uncompressed NBT data
$reader = new \Aternos\Nbt\IO\Reader\StringReader("...nbtData...", \Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

//Read gzip compressed NBT data
$gzipReader = new \Aternos\Nbt\IO\Reader\GZipCompressedStringReader("...compressedNbtData...", \Aternos\Nbt\NbtFormat::JAVA_EDITION);

//Read zlib compressed NBT data
$zlibReader = new \Aternos\Nbt\IO\Reader\ZLibCompressedStringReader("...compressedNbtData...", \Aternos\Nbt\NbtFormat::BEDROCK_EDITION_NETWORK);
```
Note that the reader object is also used to specify the NBT format flavor. 
Available are `\Aternos\Nbt\NbtFormat::JAVA_EDITION`, `\Aternos\Nbt\NbtFormat::BEDROCK_EDITION`, and `\Aternos\Nbt\NbtFormat::BEDROCK_EDITION_NETWORK`.

More advanced readers can be created by implementing the `\Aternos\Nbt\IO\Reader\Reader` interface or by extending the `\Aternos\Nbt\IO\Reader\AbstractReader` class.

A reader object can be used to load the NBT tag.
```php
$reader = new \Aternos\Nbt\IO\Reader\StringReader("...nbtData...", \Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

$tag = \Aternos\Nbt\Tag\Tag::load($reader);
```
In theory, any type of NBT tag could be returned, but in reality all NBT files
will start with either a compound tag or a list tag.

### Manipulating NBT structures
Tag values of type `TAG_Byte`, `TAG_Short`, `TAG_Int`, `TAG_Long`, `TAG_Float`,
`TAG_Double`, `TAG_String` can be accessed via their `getValue()` and `setValue()` functions.
```php
$myInt new \Aternos\Nbt\Tag\IntTag();

$myInt->setValue(42);
echo $myInt->getValue(); // 42
```

Compound tags, list tags, and array tags implement the `ArrayAccess`, `Countable`,
and `Iterator` interfaces and can therefore be accessed as arrays.
```php
$myCompound = new \Aternos\Nbt\Tag\CompoundTag();

$myCompound["myInt"] = (new \Aternos\Nbt\Tag\IntTag())->setValue(42);
$myCompound["myFloat"] = (new \Aternos\Nbt\Tag\IntTag())->setValue(42.42);
echo count($myCompound); // 2

//Manually setting a list's type is not strictly necessary,
//since it's type will be set automatically when the first element is added
$myList = (new \Aternos\Nbt\Tag\ListTag())->setContentTag(\Aternos\Nbt\Tag\TagType::TAG_String);

$myList[] = (new \Aternos\Nbt\Tag\StringTag())->setValue("Hello");
$myList[] = (new \Aternos\Nbt\Tag\StringTag())->setValue("World");
```

### Serializing NBT structures
Similar to the reader object to read NBT data, a writer object is required
to write NBT data.
```php
//Write uncompressed NBT data
$writer = (new \Aternos\Nbt\IO\Writer\StringWriter("...nbtData..."))->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

//Write gzip compressed NBT data
$gzipWriter = (new \Aternos\Nbt\IO\Writer\GZipCompressedStringWriter("...compressedNbtData..."))->setFormat(\Aternos\Nbt\NbtFormat::JAVA_EDITION);

//Write zlib compressed NBT data
$gzipWriter = (new \Aternos\Nbt\IO\Writer\ZLibCompressedStringWriter("...compressedNbtData..."))->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION_NETWORK);
```
The NBT flavor used by a writer object can differ from the one used by the 
reader object that was originally used to read the NBT structure.
It is therefore possible to use this library to convert NBT structures between the different formats.

More advanced writers can be created by implementing the `\Aternos\Nbt\IO\Writer\Writer` interface or by extending the `\Aternos\Nbt\IO\Writer\AbstractWriter` class.

A writer object can be used to write/serialize and NBT structure.
```php
$writer = (new \Aternos\Nbt\IO\Writer\StringWriter("...nbtData..."))->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

$tag->write($writer);
file_put_contents("data.nbt", $writer->getStringData());
```

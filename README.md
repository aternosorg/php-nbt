# php-nbt
A full PHP implementation of Minecraft's Named Binary Tag (NBT) format.

In contrast to other implementations, this library provides full support
for 64-bit types, including the relatively new `TAG_Long_Array`.

Additionally, all three flavors (Java Edition, Bedrock Edition/little endian, and Bedrock Edition/VarInt) of the NBT format are supported.

### Installation
```shell
composer require aternos/nbt
```

Version 2 is a major version with breaking changes.
If you are upgrading from version 1, see [Upgrading from 1.x](#upgrading-from-1x).

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
`\Aternos\Nbt\NbtFormat` is an enum with the cases `JAVA_EDITION`, `BEDROCK_EDITION`, and `BEDROCK_EDITION_NETWORK`.

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
$myInt = new \Aternos\Nbt\Tag\IntTag();

$myInt->setValue(42);
echo $myInt->getValue(); // 42
```
Values outside the range of the tag type (e.g. `128` for a `ByteTag`) throw an `\Aternos\Nbt\Exception\InvalidTagValueException`.
The same applies to the values of `ByteArrayTag` and `IntArrayTag`.

On String tags, `getValue()` and `setValue()` use the UTF-8 encoding and convert strings based on the selected NBT flavor 
when being serialized.


Compound tags, list tags, and array tags implement the `ArrayAccess` and `Countable` interfaces
and can be iterated with `foreach`, so they can be accessed as arrays.
```php
$myCompound = new \Aternos\Nbt\Tag\CompoundTag();

$myCompound["myInt"] = new \Aternos\Nbt\Tag\IntTag()->setValue(42);
$myCompound["myFloat"] = new \Aternos\Nbt\Tag\FloatTag()->setValue(42.42);
echo count($myCompound); // 2

//Manually setting a list's type is not strictly necessary,
//since its type will be set automatically when the first element is added
$myList = new \Aternos\Nbt\Tag\ListTag()->setContentType(\Aternos\Nbt\Tag\TagType::STRING);

$myList[] = new \Aternos\Nbt\Tag\StringTag()->setValue("Hello");
$myList[] = new \Aternos\Nbt\Tag\StringTag()->setValue("World");

//Array tags can also be set at once
$myBytes = new \Aternos\Nbt\Tag\ByteArrayTag()->setValues([1, 2, 3]);
echo implode(", ", $myBytes->getValues()); // 1, 2, 3
```
List and array tags always keep continuous indices:
unsetting an entry moves all following entries down, and setting an index beyond the end throws an `\Aternos\Nbt\Exception\InvalidTagValueException`.

Alternatively, compound tags can be accessed using getter/setter functions. This is especially useful in combination with
the new PHP null safe operator.
```php
/** @var \Aternos\Nbt\Tag\CompoundTag $playerDat */
$playerDat = \Aternos\Nbt\Tag\Tag::load($reader);

$playerDat->set("foo", new \Aternos\Nbt\Tag\StringTag()->setValue("bar")); //Set a value
$playerDat->delete("foo"); //Delete a value

$playerName = $playerDat->getCompound("bukkit")?->getString("lastKnownName")?->getValue();
echo $playerName ?? "Unknown player name";
```

### Serializing NBT structures
Similar to the reader object to read NBT data, a writer object is required
to write NBT data.
```php
//Write uncompressed NBT data
$writer = new \Aternos\Nbt\IO\Writer\StringWriter()->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

//Write gzip compressed NBT data
$gzipWriter = new \Aternos\Nbt\IO\Writer\GZipCompressedStringWriter()->setFormat(\Aternos\Nbt\NbtFormat::JAVA_EDITION);

//Write zlib compressed NBT data
$zlibWriter = new \Aternos\Nbt\IO\Writer\ZLibCompressedStringWriter()->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION_NETWORK);
```
The NBT flavor used by a writer object can differ from the one used by the 
reader object that was originally used to read the NBT structure.
It is therefore possible to use this library to convert NBT structures between the different formats.

More advanced writers can be created by implementing the `\Aternos\Nbt\IO\Writer\Writer` interface or by extending the `\Aternos\Nbt\IO\Writer\AbstractWriter` class.

A writer object can be used to write/serialize an NBT structure.
```php
$writer = new \Aternos\Nbt\IO\Writer\StringWriter()->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

$tag->write($writer);
file_put_contents("data.nbt", $writer->getStringData());
```

### Errors
All exceptions that this library throws because of invalid data or tag values extend `\Aternos\Nbt\Exception\NbtException`.

### Bedrock Edition level.dat
While the Bedrock Edition level.dat file is an uncompressed NBT file, 
its NBT data is prepended by two 32-bit little endian integers.

The first one seems to be the version of the Bedrock Edition Storage Tool, 
which is also stored in the `StorageVersion` tag of the NBT structure.

The second number is the size of the file's NBT structure (not including the two prepending integers).

A Bedrock Edition level.dat file could be read like this:
```php
$data = file_get_contents("level.dat");

$version = unpack("V", $data)[1];
$dataLength = unpack("V", $data, 4)[1];

if($dataLength !== strlen($data) - 8) {
    throw new Exception("Invalid level.dat data length");
}
$tag = \Aternos\Nbt\Tag\Tag::load(new \Aternos\Nbt\IO\Reader\StringReader(substr($data, 8), \Aternos\Nbt\NbtFormat::BEDROCK_EDITION));
```

## Upgrading from 1.x
Version 2 contains these breaking changes:

- `NbtFormat` is an enum instead of a class with int constants.
  The case names are the same as the old constant names (`NbtFormat::JAVA_EDITION` etc.).
  All `getFormat()`/`setFormat()` methods and reader constructors use the enum instead of `int`.
  `NbtFormat::getDeserializer()`/`getSerializer()` are replaced by `$format->createDeserializer()`/`createSerializer()`.
- `TagType` is an int-backed enum: `TagType::TAG_Byte_Array` is now `TagType::BYTE_ARRAY` etc.
  `Tag::TYPE` constants and `getType()` return `TagType`, `TagType::NAMES` is replaced by `$type->getName()`, and `Tag::TAGS`/`Tag::getTagClass()` by `$type->getTagClass()`.
- `ListTag::getContentTag()`/`setContentTag()` are renamed to `getContentType()`/`setContentType()` and use `TagType`, as does the second parameter of `CompoundTag::getList()`.
  `RawTagReadResult::getTagType()` returns `TagType` as well.
- `ByteTag`, `ShortTag` and `IntTag`, and the values of `ByteArrayTag` and `IntArrayTag`, reject values outside the range of their type with an `InvalidTagValueException` instead of silently truncating them when written.
- List and array tags keep continuous indices: unsetting an entry moves the following entries down, and setting an offset beyond the end throws an `InvalidTagValueException` instead of leaving a gap.
  Reading a missing offset returns `null` instead of triggering a warning.
- `ListTag` and the array tags implement `IteratorAggregate` instead of `Iterator`, so their `current()`, `next()`, `key()`, `valid()` and `rewind()` methods are gone.
- `ByteArrayTag`, `IntArrayTag` and `LongArrayTag` extend the new abstract `NumberArrayTag`, which adds `getValues()` and `setValues()`.
  `ArrayValueTag::checkArrayValue()` and `checkArrayKey()` are replaced by `checkValue()`, which throws an `InvalidTagValueException` instead of returning `false`.

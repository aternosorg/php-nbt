# php-nbt
A full PHP implementation of Minecraft's Named Binary Tag (NBT) format.

In contrast to other implementations, this library provides full support
for 64-bit types, including the relatively new `TAG_Long_Array`.

Additionally, all three flavors (Java Edition, Bedrock Edition/little endian, and Bedrock Edition/VarInt) of the NBT format are supported.

### Installation
```shell
composer require aternos/nbt
```

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

On String tags, `getValue()` and `setValue()` use the UTF-8 encoding and convert strings based on the selected NBT flavor 
when being serialized.


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

Alternatively, compound tags can be accessed using getter/setter functions. This is especially useful in combination with
the new PHP null safe operator.
```php
/** @var \Aternos\Nbt\Tag\CompoundTag $playerDat */
$playerDat = \Aternos\Nbt\Tag\Tag::load($reader);

$playerDat->set("foo", (new \Aternos\Nbt\Tag\StringTag())->setValue("bar")); //Set a value
$playerDat->delete("foo"); //Delete a value

$playerName = $playerDat->getCompound("bukkit")?->getString("lastKnownName")?->getValue();
echo $playerName ?? "Unknown player name";
```

### Serializing NBT structures
Similar to the reader object to read NBT data, a writer object is required
to write NBT data.
```php
//Write uncompressed NBT data
$writer = (new \Aternos\Nbt\IO\Writer\StringWriter())->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

//Write gzip compressed NBT data
$gzipWriter = (new \Aternos\Nbt\IO\Writer\GZipCompressedStringWriter())->setFormat(\Aternos\Nbt\NbtFormat::JAVA_EDITION);

//Write zlib compressed NBT data
$gzipWriter = (new \Aternos\Nbt\IO\Writer\ZLibCompressedStringWriter())->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION_NETWORK);
```
The NBT flavor used by a writer object can differ from the one used by the 
reader object that was originally used to read the NBT structure.
It is therefore possible to use this library to convert NBT structures between the different formats.

More advanced writers can be created by implementing the `\Aternos\Nbt\IO\Writer\Writer` interface or by extending the `\Aternos\Nbt\IO\Writer\AbstractWriter` class.

A writer object can be used to write/serialize an NBT structure.
```php
$writer = (new \Aternos\Nbt\IO\Writer\StringWriter())->setFormat(\Aternos\Nbt\NbtFormat::BEDROCK_EDITION);

$tag->write($writer);
file_put_contents("data.nbt", $writer->getStringData());
```

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

<?php

declare(strict_types=1);

namespace Aternos\Nbt\Tag;

class TagType
{
    public const int TAG_End = 0;
    public const int TAG_Byte = 1;
    public const int TAG_Short = 2;
    public const int TAG_Int = 3;
    public const int TAG_Long = 4;
    public const int TAG_Float = 5;
    public const int TAG_Double = 6;
    public const int TAG_Byte_Array = 7;
    public const int TAG_String = 8;
    public const int TAG_List = 9;
    public const int TAG_Compound = 10;
    public const int TAG_Int_Array = 11;
    public const int TAG_Long_Array = 12;

    public const array NAMES = [
        self::TAG_End => "TAG_End",
        self::TAG_Byte => "TAG_Byte",
        self::TAG_Short => "TAG_Short",
        self::TAG_Int => "TAG_Int",
        self::TAG_Long => "TAG_Long",
        self::TAG_Float => "TAG_Float",
        self::TAG_Double => "TAG_Double",
        self::TAG_Byte_Array => "TAG_Byte_Array",
        self::TAG_String => "TAG_String",
        self::TAG_List => "TAG_List",
        self::TAG_Compound => "TAG_Compound",
        self::TAG_Int_Array => "TAG_Int_Array",
        self::TAG_Long_Array => "TAG_Long_Array"
    ];
}

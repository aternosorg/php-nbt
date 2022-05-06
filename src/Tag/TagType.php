<?php

namespace Aternos\Nbt\Tag;

class TagType
{
    const TAG_End = 0;
    const TAG_Byte = 1;
    const TAG_Short = 2;
    const TAG_Int = 3;
    const TAG_Long = 4;
    const TAG_Float = 5;
    const TAG_Double = 6;
    const TAG_Byte_Array = 7;
    const TAG_String = 8;
    const TAG_List = 9;
    const TAG_Compound = 10;
    const TAG_Int_Array = 11;
    const TAG_Long_Array = 12;

    const NAMES = [
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

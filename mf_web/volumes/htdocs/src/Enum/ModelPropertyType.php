<?php

namespace MF\Enum;

enum ModelPropertyType
{
    case BOOL;

    case DATETIME;

    case IMAGE;

    case STORED_DATA;

    case TEXT;

    case VARCHAR;

    case UINT;
}
<?php

namespace MF\Constraint;

interface IFileConstraint extends IType
{
    const FILENAME_MAX_LENGTH = 128;

    const FILENAME_REGEX = '^(([a-z0-9])-?)*(?2)+\.(?2)+$';
}
<?php

namespace MF\Model;

interface Entity
{
    public function getId(): string;

    public function toArray(): array;
}
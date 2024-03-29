<?php

namespace App\Services\Mutators;

class MutatorsBase
{
    public $name;
    public $value;

    public static function make($name, $value = null)
    {
        $self = new self();
        $self->name = $name;
        $self->value = $value;
        return $self;
    }
}

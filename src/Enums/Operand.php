<?php

namespace Core\Enums;

enum Operand implements HasCondition
{
    case EQUAL;
    case NOT_EQUAL;
    case GREATER_THAN;
    case GREATER_THAN_OR_EQUAL;
    case LESS_THAN;
    case LESS_THAN_OR_EQUAL;


    public function name(): string
    {
        return match ($this)
        {
            self::EQUAL => '=',
            self::NOT_EQUAL => '<>',
            self::GREATER_THAN => '>',
            self::GREATER_THAN_OR_EQUAL => '>=',
            self::LESS_THAN => '<',
            self::LESS_THAN_OR_EQUAL => '<='
        };
    }
}

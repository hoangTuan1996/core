<?php

namespace Core\Enums;

enum Conditions
{
    case IN;
    case NOT_IN;
    case DATE;
    case DAY;
    case MONTH;
    case YEAR;
    case EXISTS;
    case HAS;
    case HAS_MORPH;
    case DOESNT_HAVE;
    case DOESNT_HAVE_MORPH;
    case BETWEEN;
    case BETWEEN_COLUMNS;
    case NOT_BETWEEN;
    case NOT_BETWEEN_COLUMNS;
    case RAW;
    case DATE_GREATER_THAN;
}

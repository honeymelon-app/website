<?php

namespace App\Enums;

enum ReleaseChannel: string
{
    case STABLE = 'stable';
    case BETA = 'beta';
    case ALPHA = 'alpha';
    case RC = 'rc';
}

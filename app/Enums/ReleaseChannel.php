<?php

namespace App\Enums;

enum ReleaseChannel: string
{
    case STABLE = 'stable';
    case BETA = 'beta';
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum WebhookEvent: string
{
    case ORDER_CREATED = 'order.created';
    case ORDER_UPDATED = 'order.updated';
    case ORDER_DELETED = 'order.deleted';

    case RELEASE_CREATED = 'release.created';
    case RELEASE_UPDATED = 'release.updated';
    case RELEASE_DELETED = 'release.deleted';
    case ARTIFACT_CREATED = 'artifact.created';
    case ARTIFACT_DELETED = 'artifact.deleted';
}

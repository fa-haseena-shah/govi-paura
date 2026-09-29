<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'active';
    case PendingVerification = 'pending_verification';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
    case Deleted = 'deleted';
}
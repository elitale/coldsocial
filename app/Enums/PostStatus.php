<?php

namespace App\Enums;

enum PostStatus: string
{
    case Draft = 'draft';
    case Approved = 'approved';
    case Scheduled = 'scheduled';
}

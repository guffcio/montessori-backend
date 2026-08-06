<?php

namespace App;

enum MessageNotificationStatus: string
{
    case INIT = 'init';
    case PENDING = 'pending';
    case SENT = 'sent';
    case FAILED = 'failed';
}

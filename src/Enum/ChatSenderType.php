<?php

namespace App\Enum;

enum ChatSenderType: string
{
    case Member = 'member';
    case Admin = 'admin';
}

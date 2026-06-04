<?php

namespace App\Support;

final class Roles
{
    public const ADMIN = 'Admin';
    public const OPERATORE = 'Operatore';

    public const ALL = [
        self::ADMIN,
        self::OPERATORE,
    ];
}

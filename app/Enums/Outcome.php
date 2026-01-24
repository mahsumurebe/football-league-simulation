<?php

namespace App\Enums;

enum Outcome: string
{
    case DEFAULT = 'default';
    case HOME_WIN = 'home_win';
    case DRAW = 'draw';
    case AWAY_WIN = 'away_win';
}

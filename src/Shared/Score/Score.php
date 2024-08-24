<?php

namespace Siesta\Shared\Score;

enum Score: int
{
    case NOT_YET = -1;
    case NOPE = 0;
    case LIKED = 1;
    case LOVED = 2;


}

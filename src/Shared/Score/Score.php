<?php

namespace Siesta\Shared\Score;

enum Score: int
{
    case NOT_YET = 0;
    case NOPE = -1;
    case LIKED = 1;
    case LOVED = 2;


}

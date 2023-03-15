<?php

namespace App\Services;

class SyncStatus
{
    const START = "p_start";
    const WAIT = "wait_rows";
    const FINISH = "p_end";
    const ERROR = "p_error";
}

<?php

namespace App\Services;

class SyncStatus
{
    const START = "start";
    const WAIT = "wait_rows";
    const FINISH = "end";
    const ERROR = "error";
    const STOP = "stop_full";
}

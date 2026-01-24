<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: "1.0.0",
    title: "Football League Simulation API",
    description: "API for managing and simulating football league matches"
)]
#[OA\Server(
    url: "/api",
    description: "API Server"
)]
abstract class Controller
{
    //
}

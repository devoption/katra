<?php

use App\Mcp\Servers\KatraServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('katra', KatraServer::class);

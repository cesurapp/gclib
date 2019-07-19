<?php

namespace App\Controller;

use App\Library\Controller;

class GeoController extends Controller
{
    public function index($title, $desc)
    {
        return json_encode(['asdsa' => $title, 'desc' => $desc]);
    }
}
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModeResource;
use App\Models\Mode;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ModeController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $modes = Mode::query()
            ->orderBy('name')
            ->get();

        return ModeResource::collection($modes);
    }
}

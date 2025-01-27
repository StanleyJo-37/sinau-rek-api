<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    //
    public function getAsset(int $model_id, string $model_type)
    {
        Auth::user();
        
        $asset = DB::select("
            SELECT
                a.id,
                a.path,
                a.mime_type
            FROM assets a
            LEFT JOIN asset_relations ar
                ON
                    ar.model_id = :model_id
                    AND
                    ar.model_type = :model_type
                    AND
                    ar.asset_id = a.id;
        ", [
            'model_id' => $model_id,
            'model_type' => $model_type,
        ]);

        if (empty($asset)) return null;
        else return $asset;
    }
}

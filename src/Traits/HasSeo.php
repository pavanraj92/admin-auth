<?php

namespace admin\admin_auth\Traits;

use admin\admin_auth\Models\Seo;

trait HasSeo
{
    public function saveSeo(string $modelName, int|string $modelId, array $data)
    {
        $seo = Seo::updateOrCreate(
            [
                'model_name' => $modelName,
                'model_record_id' => $modelId,
            ],
            [
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]
        );
        return $seo;
    }

    public function getSeo($model)
    {
        return Seo::where('model_name', get_class($model))
            ->where('model_record_id', $model->id)
            ->first();
    }
}
<?php

namespace App\Helpers;

use Carbon\Carbon;

use App\Models\master_holiday;

class Attachment
{
    public static function resolve_paths($rawPath, $ref = "storage"): array {
        if (!$rawPath) {
            return [];
        }

        $paths = [];

        if (is_string($rawPath) && str_starts_with($rawPath, '[')) {
            $decodedRawPath = json_decode($rawPath, true);

            if (is_array($decodedRawPath)) {
                $paths = array_merge($paths, $decodedRawPath);
            }
        } else if (is_array($rawPath)) {
            $paths = array_merge($paths, $rawPath);
        } else if ($rawPath) {
            $path[] = $rawPath;
        }

        $resolvedPaths = [];

        foreach ($paths as $item) {
            $pathStr = str_replace('\\/', '/', $item);
            $resolvedPath = null;

            if ($ref == "public") {
                $resolvedPath = public_path($pathStr);
            } else {
                if (str_starts_with($pathStr, '/')) {
                    $resolvedPath = storage_path('app/public' . $pathStr);
                } else {
                    $resolvedPath = storage_path('app/public/' . $pathStr);
                }
            }

            if (file_exists($resolvedPath)) {
                $resolvedPaths[] = $resolvedPath;
            }
        }

        return $resolvedPaths;
    }
}

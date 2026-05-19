<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Tampilkan halaman list template (web)
     */
    public function index()
    {
        $templates = Template::all();
        return view('templates', compact('templates'));
    }

    /**
     * API: Ambil semua template sebagai JSON (digunakan wizard)
     *
     * GET /api/templates
     */
    public function apiIndex(): JsonResponse
    {
        $templates = Template::all()->map(function ($template) {
            return [
                'id'            => $template->id,
                'name'          => $template->name,
                'model_url'     => $template->model_url,
                'thumbnail_url' => $template->thumbnail_url,
                'config_schema' => $template->config_schema,
                'placeholders'  => $template->placeholders,
            ];
        });

        return response()->json($templates);
    }

    /**
     * API: Detail satu template
     *
     * GET /api/templates/{id}
     */
    public function apiShow(Template $template): JsonResponse
    {
        return response()->json([
            'id'            => $template->id,
            'name'          => $template->name,
            'model_url'     => $template->model_url,
            'thumbnail_url' => $template->thumbnail_url,
            'config_schema' => $template->config_schema,
            'placeholders'  => $template->placeholders,
        ]);
    }
}

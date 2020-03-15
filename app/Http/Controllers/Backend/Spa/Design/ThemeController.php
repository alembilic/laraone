<?php

namespace App\Http\Controllers\Backend\Spa\Design;

use File;
use ThemeManager;
use Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Core\Design\Theme;
use App\Http\Resources\ThemeResource;

use App\Services\SettingsService;
use App\Services\ThemeService;

class ThemeController extends Controller
{
    protected $websiteService;
    protected $themeservice;

    public function __construct(SettingsService $websiteService, ThemeService $themeservice)
    {
        $this->websiteService = $websiteService;
        $this->themeservice = $themeservice;
    }

    public function index(Request $request)
    {
        $per_page = $request->input('per_page', 25);
        $themes = Theme::paginate($per_page);
        // $themes = Theme::all();

        $websiteSettings = $this->websiteService->getSettings();
        $activeThemeId = data_get($websiteSettings, 'website.general.activeTheme');

        return ThemeResource::collection($themes)
            ->additional([
                'activeTheme' => $activeThemeId
            ]);
    }

    public function setActive(Request $request)
    {
        $this->websiteService->updateSetting('website.general.activeTheme', $request->id);
        return response()->json(null, 200);
    }

    public function upload(Request $request) {
        if($request->hasFile('file')) {
            $file = $request->file('file');

            $rules = array('theme' => 'required|mimes:zip');
            $validator = Validator::make(array('theme'=> $file), $rules);

            if($validator->passes()) {
                // save theme zip file inside storage/app/themes and get a path+filename back
                $filename = $file->store('themes');
                $themePath = storage_path('app' . DIRECTORY_SEPARATOR . $filename);

                $return = $this->themeservice->installTheme($themePath);

                unlink($themePath);

                return response()->json([
                    'message' => $return->message,
                    'id' => $return->id
                ], $return->code);
            }
        }
    }
}

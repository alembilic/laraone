<?php

namespace App\Http\Controllers\Backend\Spa\Content;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Core\Content\ContentType;
use App\Models\Core\Content\Template;
use App\Models\Core\Content\TemplateBlock;
use App\Models\Core\Content\Content;
use App\Models\Core\Content\ContentBlock;
use App\Models\Core\Content\Block;
use App\Models\Core\Taxonomies\Taxonomy;

use Artisan;
use DB;
use Session;
use Auth;
use Timezone;
use Carbon\Carbon;
use Jenssegers\Agent\Agent;

use App\Http\Resources\ContentResource;
use App\Http\Resources\ContentTypeResource;
use App\Http\Resources\ContentCollection;
use App\Http\Resources\SettingResource;

use App\Services\SettingsService;
use App\Services\ThemeService;

class ContentController extends Controller
{
    protected $websiteService;
    protected $themeservice;

    public function __construct(SettingsService $websiteService, ThemeService $themeservice)
	{
        $this->websiteService = $websiteService;
        $this->themeservice = $themeservice;
	}

    public function index(Request $request, $contentTypeId)
    {
        $search = $request->search;
        $filter = $request->filter;
        $status = $request->status;
        $page = $request->page;
        $perPage = isset($request->per_page) ? $request->per_page : 12;
        $sort = $request->sort;

        $content = Content::with('terms')->with('author')->with('featuredimage')->whereContentTypeId($contentTypeId);

        if($sort == 'latest')
            $content = $content->latest();
        else
            $content = $content->oldest();

        if($search) {
            switch ($filter) {
                case 'username':
                    $content->whereHas('author', function($query) use($search) {
                        $query->where('firstname', 'LIKE', '%'. $search . '%');
                    });
                break;

                case 'title':
                    $content->where('title', 'LIKE', '%'. $search . '%');
                break;

                default:
                    # code...
                break;
            }
        }

        $counts = Content::whereContentTypeId($contentTypeId)
            ->selectRaw('COUNT(*) as allCount,
                         SUM(status='.Content::PUBLISH.') as publishedCount,
                         SUM(status='.Content::DRAFT.') as draftCount,
                         SUM(status='.Content::SCHEDULE.') as scheduledCount')
            ->first();

        return (new ContentCollection($content->paginate($perPage)))
            ->additional([
                'counts' => $counts,
                // 'contentTypeData' => new ContentTypeResource($contentType)
            ]);
    }

    public function getInitData(Request $request, $contentTypeId)
    {
        $contentType = ContentType::whereId($contentTypeId)->first();
        $contentTaxonomies = $contentType->taxonomies->each->setAppends(['terms', 'settings']);

        $websiteSettings = $this->websiteService->getSettings();
        $editorSettings = data_get($websiteSettings, 'admin.content.editor');

        $defaultContentLayout = get_theme_setting('content.' . lcfirst($contentType->slug) . '.layout.singlePage', null);
        $defaultContentSettings = get_theme_setting('content.' . lcfirst($contentType->slug) . '.settings', null);

        return response()->json(compact('contentTaxonomies', 'editorSettings', 'defaultContentLayout', 'defaultContentSettings'));
    }

    public function show(Request $request, $contentTypeId, $contentId)
    {
        $content = Content::with('blocks')->with('terms')->with('author')->with('featuredimage')->find($contentId);
        $contentType = ContentType::whereId($contentTypeId)->first();
        $contentTaxonomies = $contentType->taxonomies->each->setAppends(['terms', 'settings']);

        $websiteSettings = $this->websiteService->getSettings();
        $editorSettings = data_get($websiteSettings, 'admin.content.editor');
        $defaultContentSettings = get_theme_setting('content.' . lcfirst($contentType->slug) . '.settings', null);

        // merge content settings with global theme settings
        $content->settings = $content->settings ? array_merge((array)$defaultContentSettings, $content->settings->all()) : (array)$defaultContentSettings;
        $defaultContentLayout = get_theme_setting('content.' . lcfirst($contentType->slug) . '.layout.singlePage', null);

        return (new ContentResource($content))->additional(compact('contentTaxonomies', 'editorSettings', 'defaultContentLayout', 'defaultContentSettings'));
    }

    public function store(Request $request, $contentTypeId)
    {
        $content = $this->save($request, $contentTypeId);
        return new ContentResource($content);
    }

    public function update(Request $request, $contentTypeId, $contentId)
    {
        $content = $this->save($request, $contentTypeId, $contentId);
        return new ContentResource($content);
    }

    protected function save($request, $contentTypeId, $id = null)
    {
        $contentType = ContentType::whereId($contentTypeId)->first();

        // delete user removed blocks
        foreach ($request->removedItems as $key => $itemId) {
            $block = Block::where('unique_id', $itemId)->first();
            $block ? $block->delete() : null;
        }

        $content = Content::firstOrNew(['id' => $id]);
        $content->content_type_id = $contentType->id;
        $content->title = $request->title;
        $content->seo = $request->seo;
        $content->status = $request->status;
        $content->slug = $content->title;
        $content->css = $request->css;
        $content->js = $request->js;
        $content->layout = $request->layout == 'default' ? null : $request->layout;
        $content->user_id = $content->user_id ? $content->user_id : Auth::user()->id;

        if($request->status == 1)
            $content->published_at = Carbon::now();

        if($request->settings) {
            $content->settings = $this->diffContentThemeSettings($request->settings, $contentType);
        }
        $content->save();
        $content->touch();

        $content->setTaxonomies($request->taxonomiesData);

        foreach($request->blocksData as $key => $blockData) {
            $blockData = (object) $blockData;
            $this->updateOrCreateBlock($content, $blockData);
        }

        $content = Content::with('blocks')->where('id', $content->id)->first();

        return $content;
    }

    private function diffContentThemeSettings($contentSettings, $contentType)
    {
        $themeContentSettings = get_theme_setting('content.' . lcfirst($contentType->slug) . '.settings');
        $diffedSettings = array_diff_assoc($contentSettings, (array)$themeContentSettings);

        return !empty($diffedSettings) ? $diffedSettings : null;
    }

    private function updateOrCreateBlock($content, $blockData, $parentId = null)
    {
        $blockData = (object) $blockData;
        $block = $content->saveBlock($blockData, $parentId);

        // process sub blocks recursivly
        if(isset($blockData->subItems)) {
            for ($i=0; $i < count($blockData->subItems); $i++) {
                $this->updateOrCreateBlock($content, $blockData->subItems[$i], $block->unique_id);
            }
        }
    }

    public function destroy($contentTypeId, $contentId)
    {
        $content = Content::find($contentId);

        if($content->delete()) {
            return response()->json(null, 200);
        } else {
            return response()->json([
                'message' => 'Could not delete.'
            ], 403);
        }
    }

    public function setFeaturedImage(Request $request)
    {
        $content = Content::find($request->id);
        $content->featured_image_id = $request->featuredImageId;
        $content->save();
        return response()->json([
            'status' => 'success'
        ], 200);
    }

    public function removeFeaturedImage(Request $request)
    {
        $content = Content::find($request->id);
        $content->featured_image_id = null;
        $content->save();
        return response()->json([
            'status' => 'success'
        ], 200);
    }
}

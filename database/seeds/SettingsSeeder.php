<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $section = Setting::create([
            'key' => 'cms',
            'meta' => [
                'installed' => false,
                'version' => '1.0.0'
            ]
        ]);

        $section = Setting::create([
            'key' => 'website',
            'meta' => [
                'general' => [
                    'title' => 'Laraone',
                    'tagline' => 'Website tagline',
                    'url' => '',
                    'activeTheme' => 1,
                    'frontPageType' => 'welcome-page',
                    'frontPageMeta' => 1,
                    'paginationType' => 'simple',
                    'paginationPerPage' => 12
                ],
                'adminAuthPage' => [
                    'theme' => 'light',
                    'logoType' => 'text',
                    'logoText' => 'LaraOne',
                    'logoImage' => '',
                    'backgroundColor' => '',
                    'backgroundImage' => '',
                    'termsUrl' => '',
                    'privacyPolicyUrl' => '',
                    'customCss' => ''
                ],
                'comments' => [
                    'type' => 'off',
                    'loggedInToComment' => false,
                    'allowNested' => true,
                    'nestedDepth' => 1,
                    'order' => 'asc',
                    'moderation' => false,
                    'notifyOnComment' => false,
                    'notifyOnModeration' => false,
                    'disqusChannel' => ''
                ],
                'members' => [
                    'allowRegistrations' => false,
                    'defaultUserRole' => 'member',
                    'userDisplayName' => 'fullname',
                    'useRecaptcha' => false,
                    'autoApprove' => false,
                    'requireFullname' => true,
                    'requireStrongPassword' => false,
                    'newUserNotification' => false,
                    'blacklistUserNameWords' => 'admin, administrator, webmaster, moderator'
                ]
            ]
        ]);

        $section = Setting::create([
            'key' => 'admin',
            'meta' => [
                'general' => [
                    'language' => 'en',
                    'theme' => 'dark'
                ],
                'content' => [
                    'editor' => [
                        'wideLayout' => false,
                        'favoriteBlocks' => 'Headline,Text,Image,Images,Slider,Youtube',
                        'showHeaders' => false,
                        'showLabels' => true,
                        'autoSave' => false,
                        'showTaxonomies' => true,
                        'showFeaturedImage' => true,
                        'showContentDates' => false,
                        'shortcutNotifications' => true,
                        'editorNotifications' => true
                    ],
                    'indexPage' => [
                        'indexPageDisplay' => 'list',
                        'indexPageGridColumns' => 'column-3',
                        'indexPageGridStyle' => 'inline',
                        'indexPageItemsPerPage' => 12,
                        'indexPageSortBy' => 'latest',
                        'indexPageDisplayAuthor' => true,
                        'indexPageDisplayStatus' => true,
                        'indexPageDisplayCreatedUpdated' => true,
                        'indexPageDisplayFeaturedImage' => true
                    ],
                ]
            ]
        ]);

    }
}

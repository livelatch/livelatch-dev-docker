<?php

namespace App\Http\Livewire;

use Livewire\Component;

class DashboardAnalytics extends Component
{
    public int $links = 18;
    public int $clicks = 1246;
    public array $toplinks = [];
    public array $pageStats = [
        'visitors' => [
            'all' => 7432,
            'day' => 64,
            'week' => 428,
            'month' => 1830,
            'year' => 7432,
        ],
    ];
    public ?string $littlelinkName = null;
    public int $siteLinks = 1084;
    public int $siteClicks = 48216;
    public int $userNumber = 742;
    public int $lastMonthCount = 82;
    public int $lastWeekCount = 26;
    public int $last24HrsCount = 7;
    public int $updatedLast30DaysCount = 311;
    public int $updatedLast7DaysCount = 102;
    public int $updatedLast24HrsCount = 23;
    public bool $isSampleData = true;
    public string $analyticsNotice = 'Sample analytics data only. Latchalytics is coming soon.';

    public function mount(): void
    {
        $this->littlelinkName = auth()->user()?->littlelink_name ?: 'livelatch';

        $this->toplinks = [
            ['title' => 'Discord Community', 'link' => 'https://discord.com/invite/livelatch', 'click_number' => 412],
            ['title' => 'Creator Toolkit', 'link' => 'https://livelatch.com/toolkit', 'click_number' => 293],
            ['title' => 'Latest Launch Video', 'link' => 'https://youtube.com/watch?v=livelatch', 'click_number' => 248],
            ['title' => 'Sponsor Pack', 'link' => 'https://livelatch.com/sponsor-pack', 'click_number' => 177],
            ['title' => 'Email Newsletter', 'link' => 'mailto:hello@livelatch.com', 'click_number' => 116],
        ];
    }

    public function render()
    {
        return view('livewire.dashboard-analytics');
    }
}

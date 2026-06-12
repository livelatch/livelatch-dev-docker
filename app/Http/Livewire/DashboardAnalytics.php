<?php

namespace App\Http\Livewire;

use Illuminate\Support\Collection;
use Livewire\Component;

class DashboardAnalytics extends Component
{
    public int $links = 0;
    public int $clicks = 0;
    public array $toplinks = [];
    public array $pageStats = [];
    public ?string $littlelinkName = null;
    public int $siteLinks = 0;
    public int $siteClicks = 0;
    public int $userNumber = 0;
    public int $lastMonthCount = 0;
    public int $lastWeekCount = 0;
    public int $last24HrsCount = 0;
    public int $updatedLast30DaysCount = 0;
    public int $updatedLast7DaysCount = 0;
    public int $updatedLast24HrsCount = 0;

    public function mount(array $metrics = [], $toplinks = [], $pageStats = []): void
    {
        foreach ($metrics as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = is_numeric($value) ? (int) $value : $value;
            }
        }

        $this->toplinks = Collection::wrap($toplinks)
            ->filter(fn ($link) => !in_array($link->name ?? '', ['phone', 'heading'], true) && (int) ($link->button_id ?? 0) !== 96)
            ->map(fn ($link) => [
                'title' => $link->title ?? 'Untitled link',
                'link' => $link->link ?? '',
                'click_number' => (int) ($link->click_number ?? 0),
            ])
            ->values()
            ->all();

        $this->pageStats = is_array($pageStats) ? $pageStats : [];
    }

    public function render()
    {
        return view('livewire.dashboard-analytics');
    }
}

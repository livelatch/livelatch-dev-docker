@extends('layouts.sidebar')

@section('content')
<<<<<<< HEAD
    <livewire:dashboard-analytics />
=======
    <livewire:dashboard-analytics
        :metrics="[
            'links' => $links,
            'clicks' => $clicks,
            'littlelinkName' => $littlelink_name,
            'siteLinks' => $siteLinks,
            'siteClicks' => $siteClicks,
            'userNumber' => $userNumber,
            'lastMonthCount' => $lastMonthCount,
            'lastWeekCount' => $lastWeekCount,
            'last24HrsCount' => $last24HrsCount,
            'updatedLast30DaysCount' => $updatedLast30DaysCount,
            'updatedLast7DaysCount' => $updatedLast7DaysCount,
            'updatedLast24HrsCount' => $updatedLast24HrsCount,
            'isSampleData' => $isSampleData,
            'analyticsNotice' => $analyticsNotice,
        ]"
        :toplinks="$toplinks"
        :page-stats="$pageStats"
    />
>>>>>>> main
@endsection

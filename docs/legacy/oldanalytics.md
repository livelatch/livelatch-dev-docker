Use this as a reference when working on posthog stuff

public function index()
  {
    $userId = Auth::user()->id;
    $littlelink_name = Auth::user()->littlelink_name;
    $links = Link::where("user_id", $userId)->select("link")->count();
    $clicks = Link::where("user_id", $userId)->sum("click_number");
 
    $userNumber = User::count();
    $siteLinks = Link::count();
    $siteClicks = Link::sum("click_number");
 
    $users = User::select(
      "id",
      "name",
      "email",
      "created_at",
      "updated_at",
    )->get();
    $lastMonthCount = $users
      ->where("created_at", ">=", Carbon::now()->subDays(30))
      ->count();
    $lastWeekCount = $users
      ->where("created_at", ">=", Carbon::now()->subDays(7))
      ->count();
    $last24HrsCount = $users
      ->where("created_at", ">=", Carbon::now()->subHours(24))
      ->count();
    $updatedLast30DaysCount = $users
      ->where("updated_at", ">=", Carbon::now()->subDays(30))
      ->count();
    $updatedLast7DaysCount = $users
      ->where("updated_at", ">=", Carbon::now()->subDays(7))
      ->count();
    $updatedLast24HrsCount = $users
      ->where("updated_at", ">=", Carbon::now()->subHours(24))
      ->count();
 
    $links = Link::where("user_id", $userId)->select("link")->count();
    $clicks = Link::where("user_id", $userId)->sum("click_number");
    $topLinks = Link::where("user_id", $userId)
      ->orderby("click_number", "desc")
      ->whereNotNull("link")
      ->where("link", "<>", "")
      ->take(5)
      ->get();
 
    $pageStats = [
      "visitors" => [
        "all" => visits("App\Models\User", $littlelink_name)->count(),
        "day" => visits("App\Models\User", $littlelink_name)
          ->period("day")
          ->count(),
        "week" => visits("App\Models\User", $littlelink_name)
          ->period("week")
          ->count(),
        "month" => visits("App\Models\User", $littlelink_name)
          ->period("month")
          ->count(),
        "year" => visits("App\Models\User", $littlelink_name)
          ->period("year")
          ->count(),
      ],
      "os" => visits("App\Models\User", $littlelink_name)->operatingSystems(),
      "referers" => visits("App\Models\User", $littlelink_name)->refs(),
      "countries" => visits("App\Models\User", $littlelink_name)->countries(),
    ];
 
    return view("panel/index", [
      "lastMonthCount" => $lastMonthCount,
      "lastWeekCount" => $lastWeekCount,
      "last24HrsCount" => $last24HrsCount,
      "updatedLast30DaysCount" => $updatedLast30DaysCount,
      "updatedLast7DaysCount" => $updatedLast7DaysCount,
      "updatedLast24HrsCount" => $updatedLast24HrsCount,
      "toplinks" => $topLinks,
      "links" => $links,
      "clicks" => $clicks,
      "pageStats" => $pageStats,
      "littlelink_name" => $littlelink_name,
      "links" => $links,
      "clicks" => $clicks,
      "siteLinks" => $siteLinks,
      "siteClicks" => $siteClicks,
      "userNumber" => $userNumber,
    ]);
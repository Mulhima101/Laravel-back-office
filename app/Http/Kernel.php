public function __construct(WordPressService $wpService)
{
    $this->wpService = $wpService;
    $this->middleware('wordpress.auth');
}
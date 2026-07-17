<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PolicyUpdate;
use Illuminate\Support\Str;

class MonitorHrPolicies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hr:monitor-policies {--test : Run in test mode with mock data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor official sources for HR policy updates (Leave, RRACS, Salary Schedules, etc.)';

    protected $keywords = [
        'Leave',
        'RRACS',
        'Revised Rules on Administrative Cases',
        'Tranche',
        'Salary Schedule',
        'Omnibus Rules',
        'Step Increment',
        'Magna Carta',
        'Local Budget Circular',
        'National Budget Circular'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting HR Policy Monitor...');

        if ($this->option('test')) {
            $this->runTestMode();
            return;
        }

        // Real implementation would scrape DBM, CSC, or Official Gazette.
        // As a resilient example, we fetch the Official Gazette RSS feed.
        try {
            $response = Http::timeout(10)->get('https://www.officialgazette.gov.ph/feed/');
            
            if ($response->successful()) {
                $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                
                if ($xml && isset($xml->channel->item)) {
                    $foundCount = 0;
                    foreach ($xml->channel->item as $item) {
                        $title = (string) $item->title;
                        $link = (string) $item->link;
                        $pubDate = date('Y-m-d', strtotime((string) $item->pubDate));
                        $desc = strip_tags((string) $item->description);

                        // Check keywords
                        foreach ($this->keywords as $keyword) {
                            if (Str::contains(strtolower($title), strtolower($keyword)) || 
                                Str::contains(strtolower($desc), strtolower($keyword))) {
                                
                                // Check if exists
                                if (!PolicyUpdate::where('url', $link)->exists()) {
                                    PolicyUpdate::create([
                                        'title' => $title,
                                        'url' => $link,
                                        'source' => 'Official Gazette',
                                        'published_date' => $pubDate,
                                        'matched_keyword' => $keyword,
                                        'description' => Str::limit($desc, 250),
                                        'status' => 'new'
                                    ]);
                                    $this->info("Found new policy: $title (Matched: $keyword)");
                                    $foundCount++;
                                }
                                break; // Don't match multiple keywords for same item
                            }
                        }
                    }
                    $this->info("Scan complete. Found $foundCount new policies.");
                } else {
                    $this->warn('Could not parse RSS feed XML.');
                }
            } else {
                $this->error('Failed to fetch from Official Gazette. Status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('Error during monitoring: ' . $e->getMessage());
        }
    }

    private function runTestMode()
    {
        $this->info('Running in TEST mode (injecting mock policies)...');

        $mocks = [
            [
                'title' => 'CSC Resolution No. 240001: Revised Rules on Administrative Cases in the Civil Service (RRACS) 2026',
                'url' => 'https://csc.gov.ph/mock-rracs-2026',
                'source' => 'Civil Service Commission',
                'keyword' => 'RRACS',
                'desc' => 'The Civil Service Commission hereby promulgates the updated 2026 Revised Rules on Administrative Cases in the Civil Service (RRACS)...',
            ],
            [
                'title' => 'DBM Local Budget Circular No. 170: Implementation of the 3rd Tranche of the Modified Salary Schedule',
                'url' => 'https://dbm.gov.ph/mock-lbc-170-3rd-tranche',
                'source' => 'Department of Budget and Management',
                'keyword' => '3rd Tranche',
                'desc' => 'Guidelines on the grant of the 3rd Tranche of Salary Increases pursuant to Executive Order No. 64, s. 2024...',
            ],
            [
                'title' => 'Omnibus Rules on Leave Administration (Updated 2026)',
                'url' => 'https://csc.gov.ph/mock-omnibus-rules-leave',
                'source' => 'Civil Service Commission',
                'keyword' => 'Leave',
                'desc' => 'Amendments to the Omnibus Rules on Leave addressing expanded maternity and special leave privileges for local government personnel.',
            ],
        ];

        foreach ($mocks as $mock) {
            if (!PolicyUpdate::where('url', $mock['url'])->exists()) {
                PolicyUpdate::create([
                    'title' => $mock['title'],
                    'url' => $mock['url'],
                    'source' => $mock['source'],
                    'published_date' => now()->toDateString(),
                    'matched_keyword' => $mock['keyword'],
                    'description' => $mock['desc'],
                    'status' => 'new'
                ]);
                $this->info("Mock inserted: {$mock['title']}");
            } else {
                $this->warn("Mock already exists: {$mock['title']}");
            }
        }

        $this->info('Test mode complete.');
    }
}

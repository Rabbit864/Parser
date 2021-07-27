<?php

namespace App\Console\Commands;

use App\Models\ListUniversity;
use App\Modules\ParserUniversity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:listUniversities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse website www.princetonreview.com';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {

            DB::beginTransaction();

            ListUniversity::truncate();

            $parser = new ParserUniversity();

            $this->info('Begin parse website');

            $urls = $parser->parsePaginationUrl();

            foreach ($urls as $url) {

                $this->info("Begin parse {$url}");

                $universities = $parser->parseListUniversities($url);

                $this->info("End parse {$url}");

                ListUniversity::insert($universities);
            }

            $this->info('Finish parse website');

            DB::commit();
        } catch (\Exception $e) {

            Log::error("Parse Error: {$e->getMessage()}");

            DB::rollback();
        }

        return 0;
    }
}

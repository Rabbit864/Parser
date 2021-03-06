<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Modules\ParserInfoUniversity;
use App\Models\InfoUniversity;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ParseInfoUniversity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:infoUniversities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

            InfoUniversity::truncate();

            $parser = new ParserInfoUniversity();

            foreach ($parser->getUrlsPagesUniversities() as $url) {

                $university = $parser->parse($url);

                $this->info("Save: {$university}");

                $university->save();
            }

            DB::commit();

        } catch (\Exception $e) {

            $this->error("Parse Error: {$e->getMessage()}");

            Log::error("Parse Error: {$e->getMessage()}");

            DB::rollback();
        }

        return 0;
    }
}

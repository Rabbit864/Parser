<?php

namespace App\Console\Commands;

use App\Models\ListUniversity;
use App\Modules\ParserUniversity;
use Illuminate\Console\Command;

class ParserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parser:website';

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
        ListUniversity::truncate();

        $parser = new ParserUniversity();

        $this->info('Begin parse website');

        $parser->parseListUniversities();

        $this->info('Finish parse website');

        return 0;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\File_User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class Subscription extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'member:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check subscription expiration every month';

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

        $reports=File_User::all();
        foreach ($reports as $report){
            $startDate = $report->check_in;
            $endDate = Carbon::parse(Carbon::now());
            if($endDate->diffInHours($startDate)>=8)
            {
             $file=File::find($report->file_id);
                $file->update([
                    'isAvailable' => 1,
                    'reservation_holder' => null,
                ]);
                $file_user = File_User::query()->where('file_id', '=', $file->id)
                    ->where('user_id', '=', $report->user_id)
                    ->whereNull('check_out')->get()->first();
                $file_user->update([
                    'check_out' => Carbon::now()
                ]);
            }
        }
        return 0;
        }

}

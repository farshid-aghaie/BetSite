<?php

namespace App\Jobs;

use App\Enums\EForecastStatus;
use App\Enums\EForecastTypeBet365;
use App\Enums\ESportBet365;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Forecast\Forecast;

class CheckForecastsStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $forecastId;

    public function __construct($forecastId)
    {
        $this->forecastId = $forecastId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        dd('sdf');
        $forecast = Forecast::query()
            ->where('status', EForecastStatus::PENDING)
            ->where('id', $this->forecastId)
            ->first();

        if($forecast) {
            $details = json_decode($forecast->detail, true);
            $eventIdArray = array_keys($details);
            $chunkedEventIdArray = array_chunk($eventIdArray, 10);
            foreach ($chunkedEventIdArray as $key => $value) {
                $eventIds = implode($value, ',');
                $URLs[] = 'https://api.betsapi.com/v1/bet365/result?token=' . env('BETSAPI_TOKEN') . '&event_id=' . $eventIds;
            }

            $multiCurlResults = multiCurl($URLs, 'array');
            $detailsCollect = collect($details);

            $competitionResult = [];
            foreach ($multiCurlResults AS $i => $content) {
                if (isset($content['results'])) {
                    foreach ($content['results'] as $result) {
                        if ($forecastResult = $detailsCollect->where('resultId', $result['id'])->first()) {
                            switch ($forecastResult['originalLabel']) {
                                case 'Fulltime Result':
                                case 'Match Winner':
                                case 'Money Line':
                                    $competitionResult[$forecastResult['sportId']][$forecastResult['eventId']] = calcCompetitionWinner($forecastResult, $result, $forecastResult['originalLabel']);
                                    break;
                            }
                        }
                    }
                }
            }

            dump($competitionResult);
        }
    }
}

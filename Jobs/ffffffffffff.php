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
        $forecast = Forecast::query()
            ->where('status', EForecastStatus::PENDING)
            ->where('id', $this->forecastId)
            ->first();

        if($forecast) {
            $details = json_decode($forecast->detail, true);
            $eventIdArray = array_keys($details);
            $chunkedEventIdArray = array_chunk($eventIdArray, 2);
            foreach ($chunkedEventIdArray as $key => $value) {
                $eventIds = implode($value, ',');
                $URLs[] = 'https://api.betsapi.com/v1/bet365/result?token=' . env('BETSAPI_TOKEN') . '&event_id=' . $eventIds;
            }

            $ch = [];
            $mh = curl_multi_init();
            $i = 0;
            foreach ($URLs as $url) {
                $ch[$i] = curl_init();
                curl_setopt($ch[$i], CURLOPT_URL, $url);
                curl_setopt($ch[$i], CURLOPT_HEADER, 0);
                curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, true);
                curl_multi_add_handle($mh, $ch[$i]);
                $i++;
            }
            $active = null;
            do {
                curl_multi_exec($mh, $active);
                usleep(1000); // Maybe needed to limit CPU load (See P.S.)
            } while ($active);
            $results = [];
            foreach ($ch AS $i => $c) {
                $content = json_decode(curl_multi_getcontent($c), true);
                if (isset($content['results'])) {
                    $results[$i] = $content['results'];
                }
                curl_multi_remove_handle($mh, $c);
            }
            curl_multi_close($mh);

            foreach ($results as $result) {
                foreach ($result as $res) {
                    if(intval($res['time_status']) === 3)
                    {
                        if ($key = array_search($res['id'], array_column($details, 'resultId', 'eventId'))) {
                            if (intval($details[$key]['sportId']) === ESportBet365::FOOTBALL) {
                                $this->footbalCalculator($details[$key], $res);
                                dd($res);
                                dd($details[$key]);
                            }
                        }
                    }

//                elseif(intval($detail['sportId']) === ESportBet365::VOLLEYBALL)
//                {
//
//                }
//                elseif(intval($detail['sportId']) === ESportBet365::BASEBALL)
//                {
//
//                }
//                elseif(intval($detail['sportId']) === ESportBet365::HOCKEY)
//                {
//
//                }
//                elseif(intval($detail['sportId']) === ESportBet365::TENNIS)
//                {
//
//                }
                }

            }
        }

    }
//
    protected function footbalCalculator($detail, $result)
    {
        $odd = dataOdDecrypt($detail['odd'], $detail['eventId']);

        if(EForecastTypeBet365::FULLTIME_RESULT === intval($detail['forecastType']))
        {
            list($homeGoals, $awayGoals) = explode('-', trim($result['ss']));
            $homeGoals = intval($homeGoals);
            $awayGoals = intval($awayGoals);
            $forecastValue = intval($detail['forecastValue']);

            if($homeGoals > $awayGoals && $forecastValue === 1)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals < $awayGoals && $forecastValue === 2)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals === $awayGoals && $forecastValue === 0)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }
        }
        elseif(EForecastTypeBet365::DOUBLE_CHANCE === intval($detail['forecastType']))
        {
            list($homeGoals, $awayGoals) = explode('-', trim($result['ss']));
            $homeGoals = intval($homeGoals);
            $awayGoals = intval($awayGoals);
            $forecastValue = $detail['forecastValue'];

            if(($homeGoals > $awayGoals || $homeGoals == $awayGoals) && $forecastValue === '1X')
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif(($homeGoals < $awayGoals || $homeGoals == $awayGoals) && $forecastValue === 'X2')
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif(($homeGoals < $awayGoals || $homeGoals > $awayGoals) && $forecastValue === '12')
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }
        }
        elseif(EForecastTypeBet365::DRAW_NO_BET === intval($detail['forecastType']))
        {
            list($homeGoals, $awayGoals) = explode('-', trim($result['ss']));
            $homeGoals = intval($homeGoals);
            $awayGoals = intval($awayGoals);
            $forecastValue = $detail['forecastValue'];

            if($homeGoals > $awayGoals && $forecastValue === '1')
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            if($homeGoals < $awayGoals && $forecastValue === '2')
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals  == $awayGoals && ($forecastValue === '1' || $forecastValue == '2'))
            {
                $detail['forecastResult'] = EForecastStatus::DRAW;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }

        }
        elseif(EForecastTypeBet365::HALF_TIME_RESULT_BOTH_TEAMS_TO_SCORE === intval($detail['forecastType']))
        {
            $homeGoals = intval($result['scores'][1]['home']);
            $awayGoals = intval($result['scores'][1]['away']);
            list($selectedTeam, $toScore) = explode('&', trim($detail['forecastValue']));
            $selectedTeam == $result['home']['name'] ? 1 : 2;
            $toScore = trim($toScore);

            if(($homeGoals > $awayGoals && $homeGoals > 0 && $awayGoals > 0) && ($selectedTeam == 1 && $toScore == 'yes'))
            {
                $detail['forecastResult'] = EForecastStatus::WIN;

            }
            elseif(($homeGoals < $awayGoals && $homeGoals > 0 && $awayGoals > 0) && ($selectedTeam == 2 && $toScore == 'yes'))
            {
                $detail['forecastResult'] = EForecastStatus::WIN;

            }
            elseif(($homeGoals == $awayGoals && $homeGoals > 0 && $awayGoals > 0) && ($selectedTeam == 2 && $toScore == 'yes'))
            {
                $detail['forecastResult'] = EForecastStatus::WIN;

            }
            elseif(($homeGoals > $awayGoals && ($homeGoals  == 0 || $awayGoals  == 0) && ($selectedTeam == 1 && $toScore == 'no')))
            {
                $detail['forecastResult'] = EForecastStatus::WIN;

            }
            elseif(($homeGoals == $awayGoals && ($homeGoals  == 0 || $awayGoals  == 0) && ($selectedTeam == 1 && $toScore == 'no')))
            {
                $detail['forecastResult'] = EForecastStatus::WIN;

            }
            elseif(($homeGoals < $awayGoals && ($homeGoals  == 0 || $awayGoals  == 0) && ($selectedTeam == 2 && $toScore == 'no')))
            {
                $detail['forecastResult'] = EForecastStatus::WIN;

            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;

            }


        }
        elseif(EForecastTypeBet365::EXACTLY_TOTAL_GOALS === intval($detail['forecastType']))
        {
            list($homeGoals, $awayGoals) = explode('-', trim($result['ss']));
            $homeGoals = intval($homeGoals);
            $awayGoals = intval($awayGoals);
            $totalGoals = $homeGoals + $awayGoals;
            list($goals, $string) = explode(' ', trim($detail['forecastValue']));
            if($totalGoals == $goals)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif ($goals == '7+' && $totalGoals >= 7)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }

        }
        elseif(EForecastTypeBet365::ASIAN_HANDICAP === intval($detail['forecastType']))
        {
            $goals = explode('-', $result['ss']);
            $selected = $detail['forecastValue'] == 1 ? 0 : 1;
            $opponent = $detail['forecastValue'] == 1 ? 1 : 0;

            if($detail['forecastHandicap'] == '0.0')
            {
                if($goals[$selected] > $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '-0.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-0.5')
            {
                if($goals[$selected] > $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-0.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-1')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '-1.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-1.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-1.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-2')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '-2.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-2.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '+0.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }

            elseif($detail['forecastHandicap'] == '+0.5')
            {
                if($goals[$selected] > $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+0.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1')
            {

                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[1] OR $goals[$selected]  == $goals[1])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+2')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '+2.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[1] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+2.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[0]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
        }
        elseif(EForecastTypeBet365::HANDICAP === intval($detail['forecastType']))
        {
            $goals = explode('-', $result['ss']);
            $selected = $detail['forecastValue'] == 1 ? 0 : 1;
            $opponent = $detail['forecastValue'] == 1 ? 1 : 0;

            if($detail['forecastHandicap'] == '0.0')
            {
                if($goals[$selected] > $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '-0.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-0.5')
            {
                if($goals[$selected] > $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-0.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-1')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '-1.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-1.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-1.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-2')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '-2.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '-2.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '+0.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if($diffGoals >= 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }

            elseif($detail['forecastHandicap'] == '+0.5')
            {
                if($goals[$selected] > $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                else
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+0.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1')
            {

                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[1] OR $goals[$selected]  == $goals[1])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+1.75')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+2')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($detail['forecastHandicap'] == '+2.25')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[1] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }

            }
            elseif($detail['forecastHandicap'] == '+2.5')
            {
                $diffGoals = $goals[$selected] - $goals[$opponent];
                if( ($goals[$selected] > $goals[$opponent] OR $goals[0]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }

        }
        elseif(EForecastTypeBet365::EXACT_SCORE_RESULT === intval($detail['forecastType']))
        {
            list($selectedTeam, $selectedSs) = explode($detail['forecastValue'], '-');
            if(intval($selectedTeam) === 2)
            {
                list($awayGoals, $homeGoals) = explode($selectedSs, '-');
                $selectedSs = $homeGoals.'-'.$awayGoals;
            }

            if($selectedSs === $result['ss'])
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }


        }
        elseif(EForecastTypeBet365::FIRST_HALF_RESULT === intval($detail['forecastType']))
        {
            $homeGoals = intval($result['scores'][1]['home']);
            $awayGoals = intval($result['scores'][1]['away']);
            $forecastValue = intval($detail['forecastValue']);

            if($homeGoals > $awayGoals && $forecastValue === 1)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals < $awayGoals && $forecastValue === 2)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals === $awayGoals && $forecastValue === 0)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }

        }
        elseif(EForecastTypeBet365::SECOND_HALF_RESULT === intval($detail['forecastType']))
        {
            $homeGoals = intval($result['scores'][2]['home']);
            $awayGoals = intval($result['scores'][2]['away']);
            $forecastValue = intval($detail['forecastValue']);

            if($homeGoals > $awayGoals && $forecastValue === 1)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals < $awayGoals && $forecastValue === 2)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            elseif($homeGoals === $awayGoals && $forecastValue === 0)
            {
                $detail['forecastResult'] = EForecastStatus::WIN;
            }
            else
            {
                $detail['forecastResult'] = EForecastStatus::LOSE;
            }

        }
        elseif(EForecastTypeBet365::ALTERNATIVE_TOTAL_GOALS === intval($detail['forecastType']))
        {
            list($homeGoals, $awayGoals) = explode('-', trim($result['ss']));
            $homeGoals = intval($homeGoals);
            $awayGoals = intval($awayGoals);
            list($selectedTeam, $forecastValue) = explode($detail['forecastValue']);

            $selectedTeamGoals = $selectedTeam == 1 ? $homeGoals : $awayGoals;
            $opponentGoals  = $selectedTeam   == 1 ? $awayGoals : $homeGoals;
            $totalGoals = $homeGoals + $awayGoals;
            $totalGoals = intval($totalGoals);
            if($forecastValue == 'Over-0.5')
            {
                if($totalGoals === 0 || $selectedTeamGoals < $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($selectedTeamGoals > $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;

                }
            }

            elseif($forecastValue == 'Under-0.5')
            {
                if($totalGoals === 0 || $selectedTeamGoals > $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($selectedTeamGoals < $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($forecastValue == 'Over-0.75')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-0.75')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1.25')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1.25')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1.5')
            {
                if($totalGoals === 0 || $totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1.5')
            {
                if($totalGoals === 0 || $totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1.75')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1.75')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2.25')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2.25')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2.5')
            {
                if($totalGoals < 2 || $totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2.5')
            {
                if($totalGoals < 2 || $totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2.75')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2.75')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-3')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Over-3')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-3')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }

        }
        elseif(EForecastTypeBet365::FIRST_HALF_GOALS === intval($detail['forecastType']))
        {
            list($homeGoals, $awayGoals) = explode('-', trim($result['ss']));
            $homeGoals = intval($homeGoals);
            $awayGoals = intval($awayGoals);
            list($selectedTeam, $forecastValue) = explode($detail['forecastValue']);

            $selectedTeamGoals = $selectedTeam == 1 ? $homeGoals : $awayGoals;
            $opponentGoals  = $selectedTeam   == 1 ? $awayGoals : $homeGoals;
            $totalGoals = $homeGoals + $awayGoals;
            $totalGoals = intval($totalGoals);
            if($forecastValue == 'Over-0.5')
            {
                if($totalGoals === 0 || $selectedTeamGoals < $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($selectedTeamGoals > $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;

                }
            }

            elseif($forecastValue == 'Under-0.5')
            {
                if($totalGoals === 0 || $selectedTeamGoals > $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($selectedTeamGoals < $opponentGoals)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;

                }
            }
            elseif($forecastValue == 'Over-0.75')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-0.75')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1.25')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1.25')
            {
                if($totalGoals === 0)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1.5')
            {
                if($totalGoals === 0 || $totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1.5')
            {
                if($totalGoals === 0 || $totalGoals === 1)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals > 1)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-1.75')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-1.75')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2.25')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2.25')
            {
                if($totalGoals < 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2.5')
            {
                if($totalGoals < 2 || $totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2.5')
            {
                if($totalGoals < 2 || $totalGoals === 2)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                elseif($totalGoals > 2)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-2.75')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_WIN;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-2.75')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }
            elseif($forecastValue == 'Over-3')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Over-3')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
            }
            elseif($forecastValue == 'Under-3')
            {
                if($totalGoals < 3)
                {
                    $detail['forecastResult'] = EForecastStatus::WIN;
                }
                if($totalGoals  ===  3)
                {
                    $detail['forecastResult'] = EForecastStatus::DRAW;
                }
                elseif($totalGoals > 3)
                {
                    $detail['forecastResult'] = EForecastStatus::LOSE;
                }
            }

        }
        elseif(EForecastTypeBet365::BOTH_TEAMS_TO_SCORE_IN_FIRST_HALF === intval($detail['forecastType']))
        {

        }
        elseif(EForecastTypeBet365::BOTH_TEAMS_TO_SCORE_IN_SECOND_HALF === intval($detail['forecastType']))
        {

        }

        return $detail;
    }
//
//    function calculateSoccerHandicap($result)
//    {
//        if($betType[1] == 1)
//                    {
//                        if($goals[0] > $goals[1])
//                        {
//                            if($details[$result['id']]['selectedWinner'] == $result['sport_id'].'_1-1')
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//                            }
//                        }
//                        elseif($goals[1] > $goals[0])
//                        {
//                            if($details[$result['id']]['selectedWinner'] == $result['sport_id'].'_1-2')
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//                            }
//                        }
//                        else
//                        {
//                            if($details[$result['id']]['selectedWinner'] == $result['sport_id'].'_1-0')
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//                            }
//                        }
//                    }
//                    elseif($betType[1] == 2)
//                    {
//                        $selected = $bet[1] == 1 ? 0 : 1;
//                        $opponent = $bet[1] == 1 ? 1 : 0;
//                        if($details[$result['id']]['dataHandicap'] == '0.0')
//                        {
//                            if($goals[$selected] > $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::DRAW;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-0.25')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-0.5')
//                        {
//                            if($goals[$selected] > $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-0.75')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-1')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::DRAW;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-1.25')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-1.5')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-1.75')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-2')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::DRAW;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-2.25')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '-2.5')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+0.25')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if($diffGoals >= 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//
//                        elseif($details[$result['id']]['dataHandicap'] == '+0.5')
//                        {
//                            if($goals[$selected] > $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            else
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+0.75')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+1')
//                        {
//
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::DRAW;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+1.25')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( $goals[$selected] > $goals[1] OR $goals[$selected]  == $goals[1])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+1.5')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( $goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent])
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 1)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+1.75')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_LOSE;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+2')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::DRAW;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+2.25')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( ($goals[$selected] > $goals[$opponent] OR $goals[$selected]  == $goals[$opponent]) OR ($goals[$selected] < $goals[1] && $diffGoals == 1))
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::HALF_WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//
//                        }
//                        elseif($details[$result['id']]['dataHandicap'] == '+2.5')
//                        {
//                            $diffGoals = $goals[$selected] - $goals[$opponent];
//                            if( ($goals[$selected] > $goals[$opponent] OR $goals[0]  == $goals[$opponent]) OR ($goals[$selected] < $goals[$opponent] && $diffGoals == 1))
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals == 2)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::WIN;
//                            }
//                            elseif($goals[$selected] < $goals[$opponent] && $diffGoals >= 3)
//                            {
//                                $detail['forecastResult'] = EForecastStatus::LOSE;
//
//                            }
//                        }
//                    }
//    }
}
